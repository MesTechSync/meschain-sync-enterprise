<?php
/**
 * MesChain-Sync Enterprise Diagnostic Script
 *
 * This script validates system integrity and identifies potential issues
 * before installation to prevent critical errors.
 */

class MesChainDiagnostic {

    private $errors = [];
    private $warnings = [];
    private $passed = [];

    public function runCompleteDiagnostic() {
        echo "=== MesChain-Sync Enterprise v3.0.0 Diagnostic ===\n\n";

        $this->checkPhpRequirements();
        $this->checkFileStructure();
        $this->checkBootstrapDependencies();
        $this->checkAzureIntegration();
        $this->checkEventControllers();
        $this->checkTemplateFiles();
        $this->checkMarketplaceClasses();

        $this->displayResults();
    }

    private function checkPhpRequirements() {
        echo "1. Checking PHP Requirements...\n";

        $required_version = '8.0.0';
        if (version_compare(PHP_VERSION, $required_version, '>=')) {
            $this->passed[] = "PHP Version: " . PHP_VERSION . " (✓)";
        } else {
            $this->errors[] = "PHP Version: " . PHP_VERSION . " - Required: {$required_version}+";
        }

        $extensions = ['openssl', 'curl', 'json', 'mbstring', 'pdo', 'gd'];
        foreach ($extensions as $ext) {
            if (extension_loaded($ext)) {
                $this->passed[] = "Extension {$ext}: Loaded (✓)";
            } else {
                $this->errors[] = "Extension {$ext}: Missing";
            }
        }
    }

    private function checkFileStructure() {
        echo "2. Checking File Structure...\n";

        $required_files = [
            'admin/controller/extension/module/meschain_sync.php',
            'admin/model/extension/module/meschain_sync.php',
            'system/library/meschain/bootstrap.php',
            'install.xml'
        ];

        foreach ($required_files as $file) {
            if (file_exists($file)) {
                $this->passed[] = "File exists: {$file} (✓)";
            } else {
                $this->errors[] = "Missing file: {$file}";
            }
        }
    }

    private function checkBootstrapDependencies() {
        echo "3. Checking Bootstrap Dependencies...\n";

        $bootstrap_classes = [
            'system/library/meschain/security/SecurityManager.php',
            'system/library/meschain/performance/PerformanceOptimizer.php',
            'system/library/meschain/monitoring/RealtimeMonitor.php'
        ];

        foreach ($bootstrap_classes as $class_file) {
            if (file_exists($class_file)) {
                $this->passed[] = "Bootstrap class: {$class_file} (✓)";
            } else {
                $this->warnings[] = "Bootstrap class missing: {$class_file} - Will be skipped";
            }
        }
    }

    private function checkAzureIntegration() {
        echo "4. Checking Azure Integration...\n";

        $azure_files = [
            'system/library/meschain/azure/AzureManager.php',
            'admin/view/template/extension/module/meschain/azure.twig',
            'admin/language/en-gb/extension/module/meschain.php'
        ];

        $azure_missing = 0;
        foreach ($azure_files as $file) {
            if (file_exists($file)) {
                $this->passed[] = "Azure file: {$file} (✓)";
            } else {
                $this->errors[] = "CRITICAL: Azure file missing: {$file}";
                $azure_missing++;
            }
        }

        if ($azure_missing > 0) {
            $this->errors[] = "CRITICAL: Azure integration will cause fatal errors during installation!";
        }
    }

    private function checkEventControllers() {
        echo "5. Checking Event Controllers...\n";

        $controller_file = 'admin/controller/extension/module/meschain_sync.php';
        if (file_exists($controller_file)) {
            $content = file_get_contents($controller_file);

            $required_methods = [
                'product_form_event',
                'order_info_event',
                'dashboard_widget_event'
            ];

            foreach ($required_methods as $method) {
                if (strpos($content, "function {$method}") !== false) {
                    $this->passed[] = "Event method: {$method} (✓)";
                } else {
                    $this->errors[] = "Missing event method: {$method} in meschain_sync controller";
                }
            }
        }
    }

    private function checkTemplateFiles() {
        echo "6. Checking Template Files...\n";

        $templates = [
            'admin/view/template/extension/module/meschain_sync.twig',
            'admin/view/template/extension/module/meschain/marketplace/trendyol.twig',
            'admin/view/template/extension/module/meschain/marketplace/hepsiburada.twig'
        ];

        foreach ($templates as $template) {
            if (file_exists($template)) {
                $this->passed[] = "Template: {$template} (✓)";
            } else {
                $this->warnings[] = "Template missing: {$template}";
            }
        }
    }

    private function checkMarketplaceClasses() {
        echo "7. Checking Marketplace Classes...\n";

        $marketplaces = [
            'trendyol' => 'system/library/meschain/api/Trendyol.php',
            'hepsiburada' => 'system/library/meschain/api/hepsiburada.php',
            'amazon' => 'system/library/meschain/api/amazon.php',
            'ebay' => 'system/library/meschain/api/ebay.php'
        ];

        foreach ($marketplaces as $name => $file) {
            if (file_exists($file)) {
                $this->passed[] = "Marketplace API: {$name} (✓)";
            } else {
                $this->warnings[] = "Marketplace API missing: {$name}";
            }
        }
    }

    private function displayResults() {
        echo "\n=== DIAGNOSTIC RESULTS ===\n\n";

        echo "✅ PASSED (" . count($this->passed) . "):\n";
        foreach ($this->passed as $pass) {
            echo "  {$pass}\n";
        }

        echo "\n⚠️  WARNINGS (" . count($this->warnings) . "):\n";
        foreach ($this->warnings as $warning) {
            echo "  {$warning}\n";
        }

        echo "\n❌ ERRORS (" . count($this->errors) . "):\n";
        foreach ($this->errors as $error) {
            echo "  {$error}\n";
        }

        echo "\n=== SUMMARY ===\n";
        if (count($this->errors) > 0) {
            echo "❌ INSTALLATION WILL FAIL - Fix errors before proceeding!\n";
            return false;
        } elseif (count($this->warnings) > 0) {
            echo "⚠️  INSTALLATION MAY HAVE ISSUES - Review warnings\n";
            return true;
        } else {
            echo "✅ SYSTEM READY FOR INSTALLATION\n";
            return true;
        }
    }
}

// Run diagnostic if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $diagnostic = new MesChainDiagnostic();
    $result = $diagnostic->runCompleteDiagnostic();
    exit($result ? 0 : 1);
}
