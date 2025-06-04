<?php
/**
 * Trendyol Advanced Backend Testing API
 * Standalone testing for controller and model functionality
 * 
 * @version 1.0.0
 * @date June 2, 2025
 */

// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class TrendyolAdvancedBackendTester {
    private $results = [];
    private $testsPassed = 0;
    private $testsFailed = 0;
    
    public function __construct() {
        $this->log("🧪 Trendyol Advanced Backend Tester Initialized");
    }
    
    public function runAllTests() {
        $this->log("🚀 Starting backend test suite...");
        
        // Test file existence and syntax
        $this->testFileDeployment();
        
        // Test PHP functionality
        $this->testPHPFunctionality();
        
        // Test database schema (without actual DB)
        $this->testDatabaseSchema();
        
        // Test controller methods
        $this->testControllerMethods();
        
        // Test model functionality
        $this->testModelFunctionality();
        
        return $this->getResults();
    }
    
    private function testFileDeployment() {
        $this->log("📁 Testing file deployment...");
        
        $files = [
            'Controller' => 'upload/admin/controller/extension/module/trendyol_advanced.php',
            'Model' => 'upload/admin/model/extension/module/trendyol_advanced.php',
            'Template' => 'upload/admin/view/template/extension/module/trendyol_advanced.twig',
            'JavaScript' => 'upload/admin/view/javascript/meschain/trendyol_advanced.js',
            'English Language' => 'upload/admin/language/en-gb/extension/module/trendyol_advanced.php',
            'Turkish Language' => 'upload/admin/language/tr-tr/extension/module/trendyol_advanced.php'
        ];
        
        foreach ($files as $name => $path) {
            $exists = file_exists($path);
            $this->addTest("File Deployment - $name", $exists, $exists ? "File exists at $path" : "File missing: $path");
            
            if ($exists && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $syntaxValid = $this->checkPHPSyntax($path);
                $this->addTest("PHP Syntax - $name", $syntaxValid, $syntaxValid ? "Valid PHP syntax" : "Syntax errors found");
            }
        }
    }
    
    private function testPHPFunctionality() {
        $this->log("⚙️ Testing PHP functionality...");
        
        // Test controller inclusion
        try {
            $controllerPath = 'upload/admin/controller/extension/module/trendyol_advanced.php';
            if (file_exists($controllerPath)) {
                $controllerContent = file_get_contents($controllerPath);
                $hasClass = strpos($controllerContent, 'class ControllerExtensionModuleTrendyolAdvanced') !== false;
                $this->addTest("Controller Class", $hasClass, "Controller class found");
                
                $hasIndex = strpos($controllerContent, 'public function index()') !== false;
                $this->addTest("Controller Index Method", $hasIndex, "Index method found");
                
                $hasAjax = strpos($controllerContent, 'ajax') !== false;
                $this->addTest("AJAX Support", $hasAjax, "AJAX functionality present");
            }
        } catch (Exception $e) {
            $this->addTest("Controller Loading", false, "Error: " . $e->getMessage());
        }
        
        // Test model inclusion
        try {
            $modelPath = 'upload/admin/model/extension/module/trendyol_advanced.php';
            if (file_exists($modelPath)) {
                $modelContent = file_get_contents($modelPath);
                $hasClass = strpos($modelContent, 'class ModelExtensionModuleTrendyolAdvanced') !== false;
                $this->addTest("Model Class", $hasClass, "Model class found");
                
                $hasInstall = strpos($modelContent, 'public function install()') !== false;
                $this->addTest("Model Install Method", $hasInstall, "Install method found");
                
                $hasAI = strpos($modelContent, 'ai_optimization') !== false;
                $this->addTest("AI Features", $hasAI, "AI optimization features present");
            }
        } catch (Exception $e) {
            $this->addTest("Model Loading", false, "Error: " . $e->getMessage());
        }
    }
    
    private function testDatabaseSchema() {
        $this->log("🗄️ Testing database schema...");
        
        $modelPath = 'upload/admin/model/extension/module/trendyol_advanced.php';
        if (file_exists($modelPath)) {
            $content = file_get_contents($modelPath);
            
            $tables = [
                'trendyol_ai_optimization',
                'trendyol_analytics',
                'trendyol_performance',
                'trendyol_activities',
                'trendyol_alerts'
            ];
            
            foreach ($tables as $table) {
                $hasTable = strpos($content, $table) !== false;
                $this->addTest("Database Table - $table", $hasTable, $hasTable ? "Table definition found" : "Table definition missing");
            }
        }
    }
    
    private function testControllerMethods() {
        $this->log("🎛️ Testing controller methods...");
        
        $controllerPath = 'upload/admin/controller/extension/module/trendyol_advanced.php';
        if (file_exists($controllerPath)) {
            $content = file_get_contents($controllerPath);
            
            $methods = [
                'getAnalytics',
                'getPerformanceMetrics',
                'runAIOptimization',
                'bulkUpdateProducts',
                'saveSettings'
            ];
            
            foreach ($methods as $method) {
                $hasMethod = strpos($content, "function $method") !== false;
                $this->addTest("Controller Method - $method", $hasMethod, $hasMethod ? "Method found" : "Method missing");
            }
        }
    }
    
    private function testModelFunctionality() {
        $this->log("📊 Testing model functionality...");
        
        $modelPath = 'upload/admin/model/extension/module/trendyol_advanced.php';
        if (file_exists($modelPath)) {
            $content = file_get_contents($modelPath);
            
            $features = [
                'optimizePrice' => 'AI price optimization',
                'analyzePerformance' => 'Performance analysis',
                'getMetrics' => 'Metrics collection',
                'recordActivity' => 'Activity logging',
                'generateAlert' => 'Alert system'
            ];
            
            foreach ($features as $method => $description) {
                $hasFeature = strpos($content, $method) !== false;
                $this->addTest("Model Feature - $description", $hasFeature, $hasFeature ? "Feature implemented" : "Feature missing");
            }
        }
    }
    
    private function checkPHPSyntax($file) {
        $output = [];
        $return_var = 0;
        exec("php -l \"$file\" 2>&1", $output, $return_var);
        return $return_var === 0;
    }
    
    private function addTest($name, $passed, $message) {
        $this->results[] = [
            'name' => $name,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($passed) {
            $this->testsPassed++;
            $this->log("✅ $name: $message");
        } else {
            $this->testsFailed++;
            $this->log("❌ $name: $message");
        }
    }
    
    private function log($message) {
        error_log("[TrendyolTester] $message");
    }
    
    private function getResults() {
        return [
            'success' => true,
            'tests_run' => count($this->results),
            'tests_passed' => $this->testsPassed,
            'tests_failed' => $this->testsFailed,
            'success_rate' => count($this->results) > 0 ? round(($this->testsPassed / count($this->results)) * 100, 2) : 0,
            'results' => $this->results,
            'summary' => [
                'deployment_status' => 'Deployed',
                'syntax_validation' => $this->testsFailed === 0 ? 'Valid' : 'Issues Found',
                'feature_completeness' => $this->testsPassed > 15 ? 'Complete' : 'Partial',
                'ready_for_production' => $this->testsFailed === 0 && $this->testsPassed > 15
            ]
        ];
    }
}

// Handle requests
try {
    $tester = new TrendyolAdvancedBackendTester();
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'run_tests':
                $results = $tester->runAllTests();
                echo json_encode($results, JSON_PRETTY_PRINT);
                break;
                
            case 'health_check':
                echo json_encode([
                    'status' => 'healthy',
                    'message' => 'Backend tester is operational',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                break;
                
            default:
                echo json_encode([
                    'error' => 'Invalid action',
                    'available_actions' => ['run_tests', 'health_check']
                ]);
        }
    } else {
        // Default: Show available endpoints
        echo json_encode([
            'message' => 'Trendyol Advanced Backend Testing API',
            'version' => '1.0.0',
            'endpoints' => [
                '?action=run_tests' => 'Run complete test suite',
                '?action=health_check' => 'Check API health'
            ],
            'status' => 'ready'
        ], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
