<?php
/**
 * Trendyol Advanced Features Test Suite
 * Comprehensive testing of deployed advanced features
 */

echo "🧪 Trendyol Advanced Features Test Suite\n";
echo "========================================\n\n";

$tests = [];
$passed = 0;
$failed = 0;

// Test 1: File Existence
echo "📁 Testing File Existence...\n";
$files_to_check = [
    'upload/admin/controller/extension/module/trendyol_advanced.php',
    'upload/admin/model/extension/module/trendyol_advanced.php',
    'upload/admin/view/template/extension/module/trendyol_advanced.twig',
    'upload/admin/view/javascript/meschain/trendyol_advanced.js',
    'upload/admin/language/en-gb/extension/module/trendyol_advanced.php',
    'upload/admin/language/tr-tr/extension/module/trendyol_advanced.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ {$file}\n";
        $passed++;
    } else {
        echo "❌ {$file} - MISSING\n";
        $failed++;
    }
}

// Test 2: PHP Syntax Check
echo "\n🔍 Testing PHP Syntax...\n";
$php_files = [
    'upload/admin/controller/extension/module/trendyol_advanced.php',
    'upload/admin/model/extension/module/trendyol_advanced.php',
    'upload/admin/language/en-gb/extension/module/trendyol_advanced.php',
    'upload/admin/language/tr-tr/extension/module/trendyol_advanced.php'
];

foreach ($php_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l \"{$file}\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✅ {$file} - Syntax OK\n";
            $passed++;
        } else {
            echo "❌ {$file} - Syntax Error:\n";
            echo "   " . trim($output) . "\n";
            $failed++;
        }
    }
}

// Test 3: JavaScript Syntax Check (Basic)
echo "\n📜 Testing JavaScript...\n";
$js_file = 'upload/admin/view/javascript/meschain/trendyol_advanced.js';
if (file_exists($js_file)) {
    $content = file_get_contents($js_file);
    
    // Check for corrected API endpoint
    if (strpos($content, "route=extension/module/trendyol_advanced") !== false) {
        echo "✅ API endpoint corrected\n";
        $passed++;
    } else {
        echo "❌ API endpoint not corrected\n";
        $failed++;
    }
    
    // Check for class definition
    if (strpos($content, 'class TrendyolAdvanced') !== false) {
        echo "✅ TrendyolAdvanced class found\n";
        $passed++;
    } else {
        echo "❌ TrendyolAdvanced class not found\n";
        $failed++;
    }
    
    // Check for AI features
    if (strpos($content, 'aiOptimization') !== false) {
        echo "✅ AI optimization features found\n";
        $passed++;
    } else {
        echo "❌ AI optimization features not found\n";
        $failed++;
    }
}

// Test 4: Template Integration
echo "\n🎨 Testing Template Integration...\n";
$template_file = 'upload/admin/view/template/extension/module/trendyol_advanced.twig';
if (file_exists($template_file)) {
    $content = file_get_contents($template_file);
    
    // Check for JavaScript inclusion
    if (strpos($content, 'trendyol_advanced.js') !== false) {
        echo "✅ JavaScript file included in template\n";
        $passed++;
    } else {
        echo "❌ JavaScript file not included in template\n";
        $failed++;
    }
    
    // Check for dashboard elements
    if (strpos($content, 'realtime-metrics') !== false) {
        echo "✅ Real-time metrics section found\n";
        $passed++;
    } else {
        echo "❌ Real-time metrics section not found\n";
        $failed++;
    }
    
    // Check for AI features
    if (strpos($content, 'ai-optimization') !== false) {
        echo "✅ AI optimization section found\n";
        $passed++;
    } else {
        echo "❌ AI optimization section not found\n";
        $failed++;
    }
}

// Test 5: Language Files
echo "\n🌐 Testing Language Files...\n";
$lang_files = [
    'upload/admin/language/en-gb/extension/module/trendyol_advanced.php',
    'upload/admin/language/tr-tr/extension/module/trendyol_advanced.php'
];

foreach ($lang_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for required language strings
        $required_strings = [
            '_heading_title',
            '_text_ai_optimization',
            '_text_analytics',
            '_button_save'
        ];
        
        $missing_strings = [];
        foreach ($required_strings as $string) {
            if (strpos($content, $string) === false) {
                $missing_strings[] = $string;
            }
        }
        
        if (empty($missing_strings)) {
            echo "✅ {$file} - All required strings found\n";
            $passed++;
        } else {
            echo "❌ {$file} - Missing strings: " . implode(', ', $missing_strings) . "\n";
            $failed++;
        }
    }
}

// Test 6: Controller Methods
echo "\n🔧 Testing Controller Methods...\n";
$controller_file = 'upload/admin/controller/extension/module/trendyol_advanced.php';
if (file_exists($controller_file)) {
    $content = file_get_contents($controller_file);
    
    $required_methods = [
        'index',
        'install',
        'uninstall',
        'getMetrics',
        'optimizeProduct',
        'bulkOperations'
    ];
    
    foreach ($required_methods as $method) {
        if (strpos($content, "function {$method}") !== false || strpos($content, "public function {$method}") !== false) {
            echo "✅ Method {$method}() found\n";
            $passed++;
        } else {
            echo "❌ Method {$method}() not found\n";
            $failed++;
        }
    }
}

// Test 7: Model Database Schema
echo "\n🗄️ Testing Model Database Schema...\n";
$model_file = 'upload/admin/model/extension/module/trendyol_advanced.php';
if (file_exists($model_file)) {
    $content = file_get_contents($model_file);
    
    $required_tables = [
        'trendyol_ai_optimization',
        'trendyol_analytics',
        'trendyol_performance',
        'trendyol_activities',
        'trendyol_alerts'
    ];
    
    foreach ($required_tables as $table) {
        if (strpos($content, $table) !== false) {
            echo "✅ Table schema {$table} found\n";
            $passed++;
        } else {
            echo "❌ Table schema {$table} not found\n";
            $failed++;
        }
    }
}

// Test 8: File Sizes (Reasonable check)
echo "\n📊 Testing File Sizes...\n";
$size_checks = [
    'upload/admin/controller/extension/module/trendyol_advanced.php' => [10000, 50000],
    'upload/admin/model/extension/module/trendyol_advanced.php' => [20000, 100000],
    'upload/admin/view/template/extension/module/trendyol_advanced.twig' => [5000, 50000],
    'upload/admin/view/javascript/meschain/trendyol_advanced.js' => [30000, 150000]
];

foreach ($size_checks as $file => $range) {
    if (file_exists($file)) {
        $size = filesize($file);
        if ($size >= $range[0] && $size <= $range[1]) {
            echo "✅ {$file} - Size OK (" . number_format($size) . " bytes)\n";
            $passed++;
        } else {
            echo "❌ {$file} - Size unusual (" . number_format($size) . " bytes)\n";
            $failed++;
        }
    }
}

// Test Results Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 TEST RESULTS SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Passed: {$passed}\n";
echo "❌ Failed: {$failed}\n";
echo "📈 Success Rate: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED! Trendyol Advanced features are ready for deployment.\n\n";
    echo "Next Steps:\n";
    echo "1. Run database installation script\n";
    echo "2. Install module via OpenCart admin\n";
    echo "3. Configure API settings\n";
    echo "4. Test advanced features\n";
} else {
    echo "⚠️  Some tests failed. Please review the issues above before proceeding.\n";
}

echo "\n📋 Installation Scripts Available:\n";
echo "- trendyol_advanced_install.php (Standalone)\n";
echo "- install_trendyol_advanced_opencart.php (OpenCart Framework)\n";
echo "- trendyol_advanced_web_installer.html (Web Interface)\n";
echo "- trendyol_advanced_install_api.php (Web API Backend)\n";
?>
