#!/usr/bin/env php
<?php
/**
 * Trendyol Integration Tester
 * MesChain-Sync Enterprise v4.5.0
 *
 * Bu script Trendyol entegrasyonunu test eder
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Renkli çıktı için ANSI kodları
class Colors {
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const WHITE = "\033[37m";
    const RESET = "\033[0m";
    const BOLD = "\033[1m";
}

class TrendyolTester {

    private $opencart_root;
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $tests_total = 0;

    public function __construct() {
        $this->printHeader();
        $this->detectOpenCart();
    }

    private function printHeader() {
        echo Colors::CYAN . Colors::BOLD;
        echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                    MesChain-Sync Trendyol Integration                        ║\n";
        echo "║                              Test Suite v4.5.0                              ║\n";
        echo "║                                                                              ║\n";
        echo "║  Comprehensive testing for Trendyol marketplace integration                 ║\n";
        echo "║  © 2024 MesChain Technologies - All Rights Reserved                         ║\n";
        echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
        echo Colors::RESET . "\n";
    }

    private function detectOpenCart() {
        // OpenCart root detection
        $possible_paths = [
            '../upload',
            '../',
            '../../',
            '../../../'
        ];

        foreach ($possible_paths as $path) {
            if (file_exists($path . '/config.php') && file_exists($path . '/admin/config.php')) {
                $this->opencart_root = realpath($path);
                break;
            }
        }

        if (!$this->opencart_root) {
            echo Colors::RED . "❌ OpenCart installation not found!" . Colors::RESET . "\n";
            exit(1);
        }

        echo Colors::GREEN . "✓ OpenCart found: " . $this->opencart_root . Colors::RESET . "\n\n";
    }

    public function runTests() {
        echo Colors::BOLD . "🧪 Running Trendyol Integration Tests...\n" . Colors::RESET . "\n";

        // Test kategorileri
        $this->testFileStructure();
        $this->testDatabaseTables();
        $this->testApiClient();
        $this->testWebhookHandler();
        $this->testHelper();
        $this->testControllers();
        $this->testModels();
        $this->testTemplates();
        $this->testLanguages();
        $this->testPermissions();

        $this->printSummary();
    }

    private function testFileStructure() {
        $this->printTestCategory("File Structure Tests");

        $required_files = [
            'admin/controller/extension/module/meschain/trendyol.php',
            'admin/model/extension/module/meschain/trendyol.php',
            'admin/view/template/extension/module/meschain/trendyol.twig',
            'admin/language/en-gb/extension/module/meschain/trendyol.php',
            'admin/language/tr-tr/extension/module/meschain/trendyol.php',
            'system/library/meschain/api/TrendyolApiClient.php',
            'system/library/meschain/helper/TrendyolHelper.php',
            'system/library/meschain/webhook/TrendyolWebhookHandler.php',
            'catalog/controller/extension/module/trendyol_webhook.php'
        ];

        foreach ($required_files as $file) {
            $this->testFileExists($file);
        }

        $required_directories = [
            'system/library/meschain',
            'system/library/meschain/api',
            'system/library/meschain/helper',
            'system/library/meschain/webhook',
            'admin/controller/extension/module/meschain',
            'admin/model/extension/module/meschain',
            'admin/view/template/extension/module/meschain'
        ];

        foreach ($required_directories as $dir) {
            $this->testDirectoryExists($dir);
        }
    }

    private function testDatabaseTables() {
        $this->printTestCategory("Database Tests");

        try {
            require_once $this->opencart_root . '/config.php';

            $pdo = new PDO(
                'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE . ';charset=utf8',
                DB_USERNAME,
                DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $required_tables = [
                DB_PREFIX . 'trendyol_products',
                DB_PREFIX . 'trendyol_orders',
                DB_PREFIX . 'trendyol_webhook_logs',
                DB_PREFIX . 'trendyol_webhook_config'
            ];

            foreach ($required_tables as $table) {
                $this->testTableExists($pdo, $table);
            }

            $this->testPassed("Database connection successful");

        } catch (Exception $e) {
            $this->testFailed("Database connection failed: " . $e->getMessage());
        }
    }

    private function testApiClient() {
        $this->printTestCategory("API Client Tests");

        $api_file = $this->opencart_root . '/system/library/meschain/api/TrendyolApiClient.php';

        if (!file_exists($api_file)) {
            $this->testFailed("TrendyolApiClient.php not found");
            return;
        }

        require_once $api_file;

        // Class existence test
        if (class_exists('MesChain\\Api\\TrendyolApiClient')) {
            $this->testPassed("TrendyolApiClient class exists");

            // Method tests
            $reflection = new ReflectionClass('MesChain\\Api\\TrendyolApiClient');

            $required_methods = [
                '__construct',
                'testConnection',
                'getProducts',
                'getOrders',
                'updateInventory',
                'validateWebhookSignature'
            ];

            foreach ($required_methods as $method) {
                if ($reflection->hasMethod($method)) {
                    $this->testPassed("Method $method exists");
                } else {
                    $this->testFailed("Method $method missing");
                }
            }

        } else {
            $this->testFailed("TrendyolApiClient class not found");
        }
    }

    private function testWebhookHandler() {
        $this->printTestCategory("Webhook Handler Tests");

        $webhook_file = $this->opencart_root . '/system/library/meschain/webhook/TrendyolWebhookHandler.php';

        if (!file_exists($webhook_file)) {
            $this->testFailed("TrendyolWebhookHandler.php not found");
            return;
        }

        require_once $webhook_file;

        if (class_exists('MesChain\\Webhook\\TrendyolWebhookHandler')) {
            $this->testPassed("TrendyolWebhookHandler class exists");

            $reflection = new ReflectionClass('MesChain\\Webhook\\TrendyolWebhookHandler');

            $required_methods = [
                '__construct',
                'validate',
                'process'
            ];

            foreach ($required_methods as $method) {
                if ($reflection->hasMethod($method)) {
                    $this->testPassed("Method $method exists");
                } else {
                    $this->testFailed("Method $method missing");
                }
            }

        } else {
            $this->testFailed("TrendyolWebhookHandler class not found");
        }
    }

    private function testHelper() {
        $this->printTestCategory("Helper Tests");

        $helper_file = $this->opencart_root . '/system/library/meschain/helper/TrendyolHelper.php';

        if (!file_exists($helper_file)) {
            $this->testFailed("TrendyolHelper.php not found");
            return;
        }

        require_once $helper_file;

        if (class_exists('MesChain\\Helper\\TrendyolHelper')) {
            $this->testPassed("TrendyolHelper class exists");

            $reflection = new ReflectionClass('MesChain\\Helper\\TrendyolHelper');

            $required_methods = [
                '__construct',
                'formatProductForTrendyol',
                'mapCategory',
                'cleanTitle',
                'cleanDescription',
                'validateProductData'
            ];

            foreach ($required_methods as $method) {
                if ($reflection->hasMethod($method)) {
                    $this->testPassed("Method $method exists");
                } else {
                    $this->testFailed("Method $method missing");
                }
            }

        } else {
            $this->testFailed("TrendyolHelper class not found");
        }
    }

    private function testControllers() {
        $this->printTestCategory("Controller Tests");

        $controller_file = $this->opencart_root . '/admin/controller/extension/module/meschain/trendyol.php';

        if (file_exists($controller_file)) {
            $this->testPassed("Trendyol controller exists");

            $content = file_get_contents($controller_file);

            if (strpos($content, 'namespace Opencart\\Admin\\Controller\\Extension\\Module\\Meschain') !== false) {
                $this->testPassed("Controller has correct namespace");
            } else {
                $this->testFailed("Controller namespace incorrect");
            }

            if (strpos($content, 'class Trendyol extends') !== false) {
                $this->testPassed("Controller class structure correct");
            } else {
                $this->testFailed("Controller class structure incorrect");
            }

        } else {
            $this->testFailed("Trendyol controller not found");
        }

        // Webhook controller test
        $webhook_controller = $this->opencart_root . '/catalog/controller/extension/module/trendyol_webhook.php';

        if (file_exists($webhook_controller)) {
            $this->testPassed("Webhook controller exists");
        } else {
            $this->testFailed("Webhook controller not found");
        }
    }

    private function testModels() {
        $this->printTestCategory("Model Tests");

        $model_file = $this->opencart_root . '/admin/model/extension/module/meschain/trendyol.php';

        if (file_exists($model_file)) {
            $this->testPassed("Trendyol model exists");

            $content = file_get_contents($model_file);

            if (strpos($content, 'namespace Opencart\\Admin\\Model\\Extension\\Module\\Meschain') !== false) {
                $this->testPassed("Model has correct namespace");
            } else {
                $this->testFailed("Model namespace incorrect");
            }

        } else {
            $this->testFailed("Trendyol model not found");
        }
    }

    private function testTemplates() {
        $this->printTestCategory("Template Tests");

        $template_file = $this->opencart_root . '/admin/view/template/extension/module/meschain/trendyol.twig';

        if (file_exists($template_file)) {
            $this->testPassed("Trendyol template exists");

            $content = file_get_contents($template_file);

            $required_elements = [
                '{{ header }}',
                '{{ footer }}',
                'form-trendyol',
                'nav-tabs',
                'tab-general',
                'tab-api',
                'tab-products',
                'tab-webhook'
            ];

            foreach ($required_elements as $element) {
                if (strpos($content, $element) !== false) {
                    $this->testPassed("Template contains $element");
                } else {
                    $this->testFailed("Template missing $element");
                }
            }

        } else {
            $this->testFailed("Trendyol template not found");
        }
    }

    private function testLanguages() {
        $this->printTestCategory("Language Tests");

        $languages = [
            'en-gb' => 'admin/language/en-gb/extension/module/meschain/trendyol.php',
            'tr-tr' => 'admin/language/tr-tr/extension/module/meschain/trendyol.php'
        ];

        foreach ($languages as $lang => $file) {
            $full_path = $this->opencart_root . '/' . $file;

            if (file_exists($full_path)) {
                $this->testPassed("Language file exists: $lang");

                $content = file_get_contents($full_path);

                $required_strings = [
                    'heading_title',
                    'text_success',
                    'entry_api_key',
                    'entry_api_secret',
                    'entry_supplier_id',
                    'button_save',
                    'error_permission'
                ];

                foreach ($required_strings as $string) {
                    if (strpos($content, '$_[\'' . $string . '\']') !== false) {
                        $this->testPassed("Language string exists: $string");
                    } else {
                        $this->testFailed("Language string missing: $string");
                    }
                }

            } else {
                $this->testFailed("Language file not found: $lang");
            }
        }
    }

    private function testPermissions() {
        $this->printTestCategory("Permission Tests");

        $directories = [
            'system/library/meschain',
            'admin/controller/extension/module/meschain',
            'admin/model/extension/module/meschain',
            'admin/view/template/extension/module/meschain'
        ];

        foreach ($directories as $dir) {
            $full_path = $this->opencart_root . '/' . $dir;

            if (file_exists($full_path)) {
                $perms = fileperms($full_path);
                $octal = substr(sprintf('%o', $perms), -4);

                if ($octal >= '0755') {
                    $this->testPassed("Directory permissions OK: $dir ($octal)");
                } else {
                    $this->testFailed("Directory permissions insufficient: $dir ($octal)");
                }
            }
        }
    }

    // Helper test methods
    private function testFileExists($file) {
        $full_path = $this->opencart_root . '/' . $file;

        if (file_exists($full_path)) {
            $this->testPassed("File exists: " . basename($file));
        } else {
            $this->testFailed("File missing: " . $file);
        }
    }

    private function testDirectoryExists($dir) {
        $full_path = $this->opencart_root . '/' . $dir;

        if (is_dir($full_path)) {
            $this->testPassed("Directory exists: " . basename($dir));
        } else {
            $this->testFailed("Directory missing: " . $dir);
        }
    }

    private function testTableExists($pdo, $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $this->testPassed("Table exists: " . $table);
            } else {
                $this->testFailed("Table missing: " . $table);
            }
        } catch (Exception $e) {
            $this->testFailed("Table check failed: " . $table . " - " . $e->getMessage());
        }
    }

    private function printTestCategory($title) {
        echo "\n" . Colors::BOLD . Colors::BLUE;
        echo "📋 " . $title;
        echo Colors::RESET . "\n";
        echo str_repeat("─", 50) . "\n";
    }

    private function testPassed($message) {
        echo Colors::GREEN . "  ✓ " . $message . Colors::RESET . "\n";
        $this->tests_passed++;
        $this->tests_total++;
    }

    private function testFailed($message) {
        echo Colors::RED . "  ✗ " . $message . Colors::RESET . "\n";
        $this->tests_failed++;
        $this->tests_total++;
    }

    private function printSummary() {
        echo "\n" . Colors::BOLD . Colors::CYAN;
        echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                              TEST SUMMARY                                    ║\n";
        echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
        echo Colors::RESET . "\n";

        $success_rate = $this->tests_total > 0 ? round(($this->tests_passed / $this->tests_total) * 100, 2) : 0;

        echo Colors::GREEN . "✓ Tests Passed: " . $this->tests_passed . Colors::RESET . "\n";
        echo Colors::RED . "✗ Tests Failed: " . $this->tests_failed . Colors::RESET . "\n";
        echo Colors::BLUE . "📊 Total Tests: " . $this->tests_total . Colors::RESET . "\n";
        echo Colors::MAGENTA . "📈 Success Rate: " . $success_rate . "%" . Colors::RESET . "\n\n";

        if ($this->tests_failed === 0) {
            echo Colors::GREEN . Colors::BOLD;
            echo "🎉 ALL TESTS PASSED!\n";
            echo "Trendyol integration is ready to use.\n";
            echo Colors::RESET . "\n";
        } else {
            echo Colors::RED . Colors::BOLD;
            echo "❌ SOME TESTS FAILED!\n";
            echo Colors::RESET;
            echo "Please fix the issues above before using the integration.\n\n";
        }

        echo "Next steps:\n";
        echo "1. Configure Trendyol API credentials in admin panel\n";
        echo "2. Set up webhook endpoints\n";
        echo "3. Test API connection\n";
        echo "4. Start syncing products and orders\n\n";
        echo "Documentation: https://docs.meschain.com/trendyol\n";
        echo "Support: support@meschain.com\n";
    }
}

// Ana test fonksiyonu
function main() {
    echo "\n";

    // CLI kontrolü
    if (php_sapi_name() !== 'cli') {
        echo Colors::RED . "This tester must be run from command line!" . Colors::RESET . "\n";
        echo "Usage: php test_trendyol_integration.php\n";
        exit(1);
    }

    $tester = new TrendyolTester();
    $tester->runTests();
}

// Script'i çalıştır
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    main();
}
