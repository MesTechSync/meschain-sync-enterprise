<?php
/**
 * MesChain-Sync Enterprise Final Diagnostic Script
 * Validates all critical fixes and system readiness
 */

echo "=== MesChain-Sync Enterprise Final Diagnostic ===\n\n";

$errors = 0;
$warnings = 0;
$passed = 0;

// 1. Test Bootstrap File Syntax
echo "1. Testing Bootstrap File Syntax...\n";
$bootstrap_result = shell_exec('php -l system/library/meschain/bootstrap.php 2>&1');
if (strpos($bootstrap_result, 'No syntax errors') !== false) {
    echo "   ✅ Bootstrap syntax: PASSED\n";
    $passed++;
} else {
    echo "   ❌ Bootstrap syntax: FAILED\n";
    echo "   Details: " . trim($bootstrap_result) . "\n";
    $errors++;
}

// 2. Check Install.xml Syntax
echo "\n2. Testing Install.xml Syntax...\n";
$install_xml = 'install.xml';
if (file_exists($install_xml)) {
    $xml_content = file_get_contents($install_xml);
    $xml = simplexml_load_string($xml_content);
    if ($xml !== false) {
        echo "   ✅ Install.xml syntax: PASSED\n";
        $passed++;

        // Check if Azure references are removed
        if (strpos($xml_content, 'azure') === false && strpos($xml_content, 'Azure') === false) {
            echo "   ✅ Azure references removed: PASSED\n";
            $passed++;
        } else {
            echo "   ⚠️  Azure references still present: WARNING\n";
            $warnings++;
        }
    } else {
        echo "   ❌ Install.xml syntax: FAILED\n";
        $errors++;
    }
} else {
    echo "   ❌ Install.xml not found: FAILED\n";
    $errors++;
}

// 3. Check Required Files Structure
echo "\n3. Checking Required Files Structure...\n";
$required_files = [
    'admin/controller/extension/module/meschain_sync.php' => 'Main Controller',
    'admin/model/extension/module/meschain_sync.php' => 'Main Model',
    'system/library/meschain/bootstrap.php' => 'Bootstrap',
    'admin/view/template/extension/module/meschain_sync.twig' => 'Main Template'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ {$description}: FOUND\n";
        $passed++;
    } else {
        echo "   ❌ {$description}: MISSING ({$file})\n";
        $errors++;
    }
}

// 4. Check Marketplace API Files
echo "\n4. Checking Marketplace API Files...\n";
$api_files = [
    'system/library/meschain/api/Trendyol.php' => 'Trendyol API',
    'system/library/meschain/api/hepsiburada.php' => 'Hepsiburada API'
];

foreach ($api_files as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ {$description}: FOUND\n";
        $passed++;
    } else {
        echo "   ⚠️  {$description}: MISSING ({$file})\n";
        $warnings++;
    }
}

// 5. PHP Environment Check
echo "\n5. PHP Environment Check...\n";
echo "   PHP Version: " . PHP_VERSION;
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo " ✅\n";
    $passed++;
} else {
    echo " ❌ (Requires 8.0+)\n";
    $errors++;
}

$extensions = ['openssl', 'curl', 'json', 'mbstring', 'pdo'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   Extension {$ext}: ✅\n";
        $passed++;
    } else {
        echo "   Extension {$ext}: ❌\n";
        $errors++;
    }
}

// 6. Test Bootstrap Initialization
echo "\n6. Testing Bootstrap Initialization...\n";
try {
    // Create a mock registry object
    $mock_registry = new stdClass();
    $mock_registry->data = [];

    // Test if we can load the bootstrap without errors
    if (file_exists('system/library/meschain/bootstrap.php')) {
        require_once('system/library/meschain/bootstrap.php');
        echo "   ✅ Bootstrap loads successfully: PASSED\n";
        $passed++;

        // Test version method
        if (method_exists('MesChain\Core\Bootstrap', 'getVersion')) {
            $version = \MesChain\Core\Bootstrap::getVersion();
            echo "   ✅ Version method works: PASSED (v{$version})\n";
            $passed++;
        } else {
            echo "   ❌ Version method: FAILED\n";
            $errors++;
        }
    } else {
        echo "   ❌ Bootstrap file not found: FAILED\n";
        $errors++;
    }
} catch (Exception $e) {
    echo "   ❌ Bootstrap initialization: FAILED - " . $e->getMessage() . "\n";
    $errors++;
}

// Final Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "FINAL DIAGNOSTIC SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "✅ PASSED: {$passed}\n";
echo "⚠️  WARNINGS: {$warnings}\n";
echo "❌ ERRORS: {$errors}\n\n";

if ($errors == 0) {
    echo "🎉 SYSTEM READY FOR INSTALLATION!\n";
    echo "The RESTRUCTURED_UPLOAD folder is ready to be deployed.\n\n";
    echo "Installation Steps:\n";
    echo "1. Upload files to OpenCart root directory\n";
    echo "2. Install via Extensions > Extension Installer\n";
    echo "3. Enable via Extensions > Modules > MesChain Sync\n";
    exit(0);
} elseif ($errors <= 2) {
    echo "⚠️  MINOR ISSUES DETECTED\n";
    echo "The system can likely be installed but may have limited functionality.\n";
    exit(1);
} else {
    echo "❌ CRITICAL ISSUES DETECTED\n";
    echo "Fix the errors above before attempting installation.\n";
    exit(2);
}
