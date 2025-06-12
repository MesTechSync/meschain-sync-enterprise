<?php
/**
 * MesChain API Infrastructure Validation Script
 * Quick validation of all API components without database operations
 * 
 * @version 1.0.0
 * @date June 2, 2025
 * @author MesChain Development Team
 */

class MeschainInfrastructureValidator {
    
    private $validation_log = [];
    private $start_time;
    
    public function __construct() {
        $this->start_time = microtime(true);
    }
    
    /**
     * Main validation process
     */
    public function validate() {
        $this->log('info', 'Starting MesChain API Infrastructure Validation');
        
        try {
            $this->validateApiComponents();
            $this->validateControllerUpdates();
            $this->validateFilePermissions();
            $this->generateValidationReport();
            
            $this->log('success', 'Infrastructure validation completed successfully');
            return $this->getValidationReport();
            
        } catch (Exception $e) {
            $this->log('error', 'Validation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validate API components
     */
    private function validateApiComponents() {
        $this->log('info', 'Validating API components...');
        
        $required_files = [
            'upload/system/library/meschain/api_error_handler.php' => 'Error handling system',
            'upload/system/library/meschain/database_manager.php' => 'Database integration manager',
            'upload/system/library/meschain/api_response_formatter.php' => 'Response formatting',
            'upload/system/library/meschain/advanced_rate_limiter.php' => 'Rate limiting system',
            'upload/system/library/meschain/api_integration_service.php' => 'Integration service',
            'upload/system/library/meschain/api_test_suite.php' => 'Testing framework'
        ];
        
        foreach ($required_files as $file => $description) {
            if (file_exists($file) && is_readable($file)) {
                $this->log('success', "✓ {$description}: {$file}");
            } else {
                $this->log('error', "✗ Missing {$description}: {$file}");
                throw new Exception("Required component missing: {$file}");
            }
        }
    }
    
    /**
     * Validate controller updates
     */
    private function validateControllerUpdates() {
        $this->log('info', 'Validating marketplace API controllers...');
        
        $controllers = [
            'upload/admin/controller/extension/module/meschain_dashboard_api.php' => 'Dashboard API',
            'upload/admin/controller/extension/module/amazon_api.php' => 'Amazon API',
            'upload/admin/controller/extension/module/ebay_api.php' => 'eBay API',
            'upload/admin/controller/extension/module/trendyol_api.php' => 'Trendyol API',
            'upload/admin/controller/extension/module/n11_api.php' => 'N11 API',
            'upload/admin/controller/extension/module/hepsiburada_api.php' => 'Hepsiburada API',
            'upload/admin/controller/extension/module/ozon_api.php' => 'Ozon API'
        ];
        
        foreach ($controllers as $file => $name) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                
                // Check for infrastructure integration
                $has_load_infrastructure = strpos($content, 'loadInfrastructure()') !== false;
                $has_send_response = strpos($content, 'sendResponse(') !== false;
                $has_integration_service = strpos($content, 'integration_service') !== false;
                
                if ($has_load_infrastructure && $has_send_response && $has_integration_service) {
                    $this->log('success', "✓ {$name}: Infrastructure integrated");
                } else {
                    $this->log('warning', "⚠ {$name}: Partial infrastructure integration");
                }
                
                // Check for legacy methods still in use
                $legacy_methods = ['handleApiError(', 'setJsonHeaders()'];
                $legacy_count = 0;
                foreach ($legacy_methods as $method) {
                    $legacy_count += substr_count($content, $method);
                }
                
                if ($legacy_count > 2) { // Allow method definitions
                    $this->log('warning', "⚠ {$name}: {$legacy_count} legacy method calls remaining");
                } else {
                    $this->log('success', "✓ {$name}: Legacy methods updated");
                }
                
            } else {
                $this->log('error', "✗ Missing controller: {$file}");
            }
        }
    }
    
    /**
     * Validate file permissions
     */
    private function validateFilePermissions() {
        $this->log('info', 'Validating file permissions...');
        
        $directories = [
            'upload/system/library/meschain',
            'upload/admin/controller/extension/module'
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir) && is_readable($dir)) {
                $this->log('success', "✓ Directory accessible: {$dir}");
            } else {
                $this->log('warning', "⚠ Directory not accessible: {$dir}");
            }
        }
    }
    
    /**
     * Generate validation report
     */
    private function generateValidationReport() {
        $this->log('info', 'Generating validation report...');
        
        $success_count = count(array_filter($this->validation_log, function($entry) {
            return $entry['level'] === 'success';
        }));
        
        $warning_count = count(array_filter($this->validation_log, function($entry) {
            return $entry['level'] === 'warning';
        }));
        
        $error_count = count(array_filter($this->validation_log, function($entry) {
            return $entry['level'] === 'error';
        }));
        
        $total_checks = count($this->validation_log);
        $success_rate = $total_checks > 0 ? round(($success_count / $total_checks) * 100, 2) : 0;
        
        $report = [
            'validation_date' => date('Y-m-d H:i:s'),
            'execution_time' => round((microtime(true) - $this->start_time) * 1000, 2),
            'total_checks' => $total_checks,
            'success_count' => $success_count,
            'warning_count' => $warning_count,
            'error_count' => $error_count,
            'success_rate' => $success_rate,
            'status' => $error_count === 0 ? 'PASS' : 'FAIL',
            'log_entries' => $this->validation_log
        ];
        
        file_put_contents('MESCHAIN_INFRASTRUCTURE_VALIDATION_REPORT.json', json_encode($report, JSON_PRETTY_PRINT));
        $this->log('success', 'Validation report generated');
    }
    
    /**
     * Log validation activity
     */
    private function log($level, $message) {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'execution_time' => round((microtime(true) - $this->start_time) * 1000, 2)
        ];
        
        $this->validation_log[] = $entry;
        
        // Output to console with colors
        $colors = [
            'success' => "\033[32m",  // Green
            'warning' => "\033[33m",  // Yellow
            'error' => "\033[31m",    // Red
            'info' => "\033[34m",     // Blue
            'reset' => "\033[0m"      // Reset
        ];
        
        $color = $colors[$level] ?? $colors['info'];
        echo $color . "[{$entry['timestamp']}] [{$level}] {$message}" . $colors['reset'] . "\n";
    }
    
    /**
     * Get validation report
     */
    public function getValidationReport() {
        $execution_time = round((microtime(true) - $this->start_time) * 1000, 2);
        
        $success_count = count(array_filter($this->validation_log, function($entry) {
            return $entry['level'] === 'success';
        }));
        
        $total_checks = count($this->validation_log);
        $success_rate = $total_checks > 0 ? round(($success_count / $total_checks) * 100, 2) : 0;
        
        return [
            'status' => $success_rate >= 80 ? 'success' : 'warning',
            'execution_time' => $execution_time,
            'success_rate' => $success_rate,
            'total_checks' => $total_checks,
            'success_count' => $success_count
        ];
    }
}

// Execute validation if called directly
if (php_sapi_name() === 'cli' || (isset($_GET['validate']) && $_GET['validate'] === 'true')) {
    echo "\nMesChain API Infrastructure Validation\n";
    echo "=====================================\n\n";
    
    try {
        $validator = new MeschainInfrastructureValidator();
        $report = $validator->validate();
        
        echo "\n\n=== VALIDATION RESULTS ===\n";
        echo "Status: " . strtoupper($report['status']) . "\n";
        echo "Execution Time: {$report['execution_time']}ms\n";
        echo "Success Rate: {$report['success_rate']}%\n";
        echo "Total Checks: {$report['total_checks']}\n";
        echo "Successful: {$report['success_count']}\n";
        
        if ($report['success_rate'] >= 90) {
            echo "\n🎉 Infrastructure is ready for production!\n";
        } elseif ($report['success_rate'] >= 80) {
            echo "\n⚠️  Infrastructure has minor issues but is functional.\n";
        } else {
            echo "\n❌ Infrastructure has significant issues that need attention.\n";
        }
        
    } catch (Exception $e) {
        echo "\n\n=== VALIDATION FAILED ===\n";
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "MesChain API Infrastructure Validation Script\n";
    echo "Usage: php validate_infrastructure.php or access via web with ?validate=true\n";
}
?>
