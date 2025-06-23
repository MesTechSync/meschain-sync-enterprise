<?php

/**
 * MesChain Trendyol Integration - Test Runner
 * Comprehensive test execution and reporting
 *
 * @version 1.0.0
 * @author MesChain Development Team
 * @date June 21, 2025
 */

class TestRunner
{
    private $testSuites = [
        'unit' => [
            'name' => 'Unit Tests',
            'description' => 'Core functionality unit tests',
            'files' => ['unit/TrendyolClientTest.php']
        ],
        'integration' => [
            'name' => 'Integration Tests',
            'description' => 'Full system integration tests',
            'files' => ['integration/TrendyolIntegrationTest.php']
        ],
        'e2e' => [
            'name' => 'End-to-End Tests',
            'description' => 'Complete user journey tests',
            'files' => ['e2e/TrendyolE2ETest.php']
        ],
        'performance' => [
            'name' => 'Performance Tests',
            'description' => 'Load testing and performance benchmarks',
            'files' => ['performance/PerformanceTest.php']
        ],
        'security' => [
            'name' => 'Security Tests',
            'description' => 'Security audit and vulnerability assessment',
            'files' => ['security/SecurityAuditTest.php']
        ]
    ];

    private $results = [];
    private $startTime;
    private $reportsDir;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->reportsDir = __DIR__ . '/../reports';
        $this->ensureDirectoryExists($this->reportsDir);
    }

    public function run($suiteType = 'all')
    {
        echo "MesChain Trendyol Integration - Test Runner v1.0.0\n";
        echo "================================================\n\n";

        if ($suiteType === 'all') {
            $this->runAllTests();
        } elseif (isset($this->testSuites[$suiteType])) {
            $this->runTestSuite($suiteType);
        } else {
            echo "Error: Unknown test suite '$suiteType'\n";
            echo "Available suites: " . implode(', ', array_keys($this->testSuites)) . ", all\n";
            exit(1);
        }

        $this->generateReport();
        $this->printSummary();

        return $this->getExitCode();
    }

    private function runAllTests()
    {
        echo "Running all test suites...\n\n";

        foreach ($this->testSuites as $type => $suite) {
            $this->runTestSuite($type);
            echo "\n";
        }
    }

    private function runTestSuite($type)
    {
        $suite = $this->testSuites[$type];

        echo "Running {$suite['name']}...\n";
        echo "Description: {$suite['description']}\n";
        echo str_repeat('-', 50) . "\n";

        $suiteResults = [
            'name' => $suite['name'],
            'type' => $type,
            'start_time' => microtime(true),
            'tests' => [],
            'passed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($suite['files'] as $testFile) {
            $testResult = $this->runTestFile($testFile);
            $suiteResults['tests'][] = $testResult;

            if ($testResult['status'] === 'passed') {
                $suiteResults['passed']++;
                echo "✓ {$testResult['name']} - PASSED\n";
            } elseif ($testResult['status'] === 'failed') {
                $suiteResults['failed']++;
                echo "✗ {$testResult['name']} - FAILED\n";
                if (!empty($testResult['error'])) {
                    echo "  Error: {$testResult['error']}\n";
                    $suiteResults['errors'][] = $testResult['error'];
                }
            } else {
                $suiteResults['skipped']++;
                echo "- {$testResult['name']} - SKIPPED\n";
            }
        }

        $suiteResults['end_time'] = microtime(true);
        $suiteResults['duration'] = $suiteResults['end_time'] - $suiteResults['start_time'];

        $this->results[$type] = $suiteResults;

        echo str_repeat('-', 50) . "\n";
        echo "Suite Summary: {$suiteResults['passed']} passed, {$suiteResults['failed']} failed, {$suiteResults['skipped']} skipped\n";
        echo "Duration: " . number_format($suiteResults['duration'], 2) . " seconds\n";
    }

    private function runTestFile($testFile)
    {
        $fullPath = __DIR__ . '/' . $testFile;
        $testName = basename($testFile, '.php');

        $result = [
            'name' => $testName,
            'file' => $testFile,
            'start_time' => microtime(true),
            'status' => 'skipped',
            'error' => null,
            'output' => ''
        ];

        if (!file_exists($fullPath)) {
            $result['error'] = "Test file not found: $fullPath";
            $result['status'] = 'failed';
            return $result;
        }

        try {
            // Capture output
            ob_start();

            // Include and run the test
            $testResult = $this->executeTest($fullPath);

            $result['output'] = ob_get_clean();
            $result['status'] = $testResult ? 'passed' : 'failed';
        } catch (Exception $e) {
            ob_end_clean();
            $result['error'] = $e->getMessage();
            $result['status'] = 'failed';
        } catch (Error $e) {
            ob_end_clean();
            $result['error'] = $e->getMessage();
            $result['status'] = 'failed';
        }

        $result['end_time'] = microtime(true);
        $result['duration'] = $result['end_time'] - $result['start_time'];

        return $result;
    }

    private function executeTest($testFile)
    {
        // Set up test environment
        $this->setupTestEnvironment();

        try {
            // Include the test file
            require_once $testFile;

            // Get the test class name from file
            $className = $this->getTestClassName($testFile);

            if (!class_exists($className)) {
                throw new Exception("Test class $className not found in $testFile");
            }

            // Create test instance
            $testInstance = new $className();

            // Run test methods
            $methods = get_class_methods($testInstance);
            $testMethods = array_filter($methods, function ($method) {
                return strpos($method, 'test') === 0;
            });

            $passed = 0;
            $total = count($testMethods);

            foreach ($testMethods as $method) {
                try {
                    // Setup
                    if (method_exists($testInstance, 'setUp')) {
                        $testInstance->setUp();
                    }

                    // Run test
                    $testInstance->$method();
                    $passed++;

                    // Teardown
                    if (method_exists($testInstance, 'tearDown')) {
                        $testInstance->tearDown();
                    }
                } catch (Exception $e) {
                    // Test failed
                    if (method_exists($testInstance, 'tearDown')) {
                        $testInstance->tearDown();
                    }
                    throw $e;
                }
            }

            return $passed === $total;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getTestClassName($testFile)
    {
        $content = file_get_contents($testFile);

        // Extract class name using regex
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        // Fallback to filename
        return basename($testFile, '.php');
    }

    private function setupTestEnvironment()
    {
        // Set test environment variables
        $_ENV['TESTING'] = true;
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_NAME'] = 'test_opencart';
        $_ENV['DB_USER'] = 'test_user';
        $_ENV['DB_PASS'] = 'test_pass';

        // Mock functions if needed
        if (!function_exists('mock_function')) {
            function mock_function($name, $return = null)
            {
                return $return;
            }
        }
    }

    private function generateReport()
    {
        $reportData = [
            'test_run' => [
                'start_time' => $this->startTime,
                'end_time' => microtime(true),
                'duration' => microtime(true) - $this->startTime,
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
            ],
            'summary' => $this->calculateSummary(),
            'suites' => $this->results
        ];

        // Generate JSON report
        $jsonReport = json_encode($reportData, JSON_PRETTY_PRINT);
        file_put_contents($this->reportsDir . '/test_results_' . date('Y-m-d_H-i-s') . '.json', $jsonReport);

        // Generate HTML report
        $this->generateHtmlReport($reportData);

        // Generate JUnit XML report
        $this->generateJUnitReport($reportData);
    }

    private function calculateSummary()
    {
        $summary = [
            'total_suites' => count($this->results),
            'total_tests' => 0,
            'total_passed' => 0,
            'total_failed' => 0,
            'total_skipped' => 0,
            'success_rate' => 0
        ];

        foreach ($this->results as $suite) {
            $summary['total_tests'] += count($suite['tests']);
            $summary['total_passed'] += $suite['passed'];
            $summary['total_failed'] += $suite['failed'];
            $summary['total_skipped'] += $suite['skipped'];
        }

        if ($summary['total_tests'] > 0) {
            $summary['success_rate'] = round(($summary['total_passed'] / $summary['total_tests']) * 100, 2);
        }

        return $summary;
    }

    private function generateHtmlReport($reportData)
    {
        $summary = $reportData['summary'];
        $duration = number_format($reportData['test_run']['duration'], 2);

        $html = "
<!DOCTYPE html>
<html>
<head>
    <title>MesChain Trendyol Integration - Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f4f4f4; padding: 20px; border-radius: 5px; }
        .summary { display: flex; gap: 20px; margin: 20px 0; }
        .metric { background: #e9ecef; padding: 15px; border-radius: 5px; text-align: center; }
        .metric h3 { margin: 0; color: #495057; }
        .metric .value { font-size: 24px; font-weight: bold; margin: 5px 0; }
        .passed { color: #28a745; }
        .failed { color: #dc3545; }
        .skipped { color: #ffc107; }
        .suite { margin: 20px 0; border: 1px solid #dee2e6; border-radius: 5px; }
        .suite-header { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #dee2e6; }
        .suite-content { padding: 15px; }
        .test-item { padding: 10px; border-bottom: 1px solid #f1f3f4; }
        .test-item:last-child { border-bottom: none; }
        .status-passed { color: #28a745; }
        .status-failed { color: #dc3545; }
        .status-skipped { color: #6c757d; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>Test Report - MesChain Trendyol Integration</h1>
        <p><strong>Date:</strong> {$reportData['test_run']['timestamp']}</p>
        <p><strong>Duration:</strong> {$duration} seconds</p>
        <p><strong>Success Rate:</strong> {$summary['success_rate']}%</p>
    </div>

    <div class='summary'>
        <div class='metric'>
            <h3>Total Tests</h3>
            <div class='value'>{$summary['total_tests']}</div>
        </div>
        <div class='metric'>
            <h3>Passed</h3>
            <div class='value passed'>{$summary['total_passed']}</div>
        </div>
        <div class='metric'>
            <h3>Failed</h3>
            <div class='value failed'>{$summary['total_failed']}</div>
        </div>
        <div class='metric'>
            <h3>Skipped</h3>
            <div class='value skipped'>{$summary['total_skipped']}</div>
        </div>
    </div>";

        foreach ($reportData['suites'] as $suite) {
            $suiteDuration = number_format($suite['duration'], 2);
            $html .= "
    <div class='suite'>
        <div class='suite-header'>
            <h2>{$suite['name']}</h2>
            <p>Duration: {$suiteDuration}s | Passed: {$suite['passed']} | Failed: {$suite['failed']} | Skipped: {$suite['skipped']}</p>
        </div>
        <div class='suite-content'>";

            foreach ($suite['tests'] as $test) {
                $statusClass = "status-{$test['status']}";
                $testDuration = number_format($test['duration'], 3);
                $html .= "
            <div class='test-item'>
                <span class='$statusClass'>● {$test['name']}</span>
                <span style='float: right;'>{$testDuration}s</span>";

                if ($test['error']) {
                    $html .= "<br><small style='color: #dc3545;'>Error: " . htmlspecialchars($test['error']) . "</small>";
                }

                $html .= "</div>";
            }

            $html .= "
        </div>
    </div>";
        }

        $html .= "
</body>
</html>";

        file_put_contents($this->reportsDir . '/test_report_' . date('Y-m-d_H-i-s') . '.html', $html);
    }

    private function generateJUnitReport($reportData)
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $testsuites = $xml->createElement('testsuites');
        $testsuites->setAttribute('name', 'MesChain Trendyol Integration');
        $testsuites->setAttribute('tests', $reportData['summary']['total_tests']);
        $testsuites->setAttribute('failures', $reportData['summary']['total_failed']);
        $testsuites->setAttribute('time', number_format($reportData['test_run']['duration'], 3));

        foreach ($reportData['suites'] as $suite) {
            $testsuite = $xml->createElement('testsuite');
            $testsuite->setAttribute('name', $suite['name']);
            $testsuite->setAttribute('tests', count($suite['tests']));
            $testsuite->setAttribute('failures', $suite['failed']);
            $testsuite->setAttribute('time', number_format($suite['duration'], 3));

            foreach ($suite['tests'] as $test) {
                $testcase = $xml->createElement('testcase');
                $testcase->setAttribute('name', $test['name']);
                $testcase->setAttribute('classname', $suite['type']);
                $testcase->setAttribute('time', number_format($test['duration'], 3));

                if ($test['status'] === 'failed') {
                    $failure = $xml->createElement('failure');
                    $failure->setAttribute('message', $test['error'] ?: 'Test failed');
                    $testcase->appendChild($failure);
                }

                if ($test['status'] === 'skipped') {
                    $skipped = $xml->createElement('skipped');
                    $testcase->appendChild($skipped);
                }

                $testsuite->appendChild($testcase);
            }

            $testsuites->appendChild($testsuite);
        }

        $xml->appendChild($testsuites);
        $xml->save($this->reportsDir . '/junit_results_' . date('Y-m-d_H-i-s') . '.xml');
    }

    private function printSummary()
    {
        $summary = $this->calculateSummary();
        $duration = number_format(microtime(true) - $this->startTime, 2);

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat('=', 60) . "\n";
        echo "Total Suites: {$summary['total_suites']}\n";
        echo "Total Tests:  {$summary['total_tests']}\n";
        echo "Passed:       {$summary['total_passed']}\n";
        echo "Failed:       {$summary['total_failed']}\n";
        echo "Skipped:      {$summary['total_skipped']}\n";
        echo "Success Rate: {$summary['success_rate']}%\n";
        echo "Duration:     {$duration} seconds\n";
        echo str_repeat('=', 60) . "\n";

        if ($summary['total_failed'] > 0) {
            echo "\nFAILED TESTS:\n";
            foreach ($this->results as $suite) {
                if (!empty($suite['errors'])) {
                    echo "- {$suite['name']}:\n";
                    foreach ($suite['errors'] as $error) {
                        echo "  * $error\n";
                    }
                }
            }
        }

        echo "\nReports generated in: {$this->reportsDir}/\n";
    }

    private function getExitCode()
    {
        $summary = $this->calculateSummary();
        return $summary['total_failed'] > 0 ? 1 : 0;
    }

    private function ensureDirectoryExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $suiteType = isset($argv[1]) ? $argv[1] : 'all';
    $runner = new TestRunner();
    exit($runner->run($suiteType));
}
