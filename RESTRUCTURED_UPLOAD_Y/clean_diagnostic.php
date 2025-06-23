<?php
/**
 * MesChain-Sync Enterprise Clean Diagnostic Script
 * Identifies critical PHP syntax errors and missing dependencies
 */

echo "=== MesChain-Sync Enterprise Critical Error Diagnostic ===\n\n";

// 1. Critical Bootstrap Issue
echo "1. CRITICAL: Bootstrap Registry Issue\n";
$bootstrap_file = 'system/library/meschain/bootstrap.php';
if (file_exists($bootstrap_file)) {
    $content = file_get_contents($bootstrap_file);
    if (strpos($content, "isset(\$registry->get('event'))") !== false) {
        echo "   ❌ FATAL ERROR: Line 89 - Cannot use return value in write context\n";
        echo "   🔧 FIX: Change to: if (\$registry->has('event'))\n\n";
    }
} else {
    echo "   ❌ Bootstrap file missing\n\n";
}

// 2. Azure Integration Missing
echo "2. CRITICAL: Azure Integration Missing\n";
$azure_files = [
    'system/library/meschain/azure/AzureManager.php',
    'admin/view/template/extension/module/meschain/azure.twig'
];

$azure_missing = 0;
foreach ($azure_files as $file) {
    if (!file_exists($file)) {
        echo "   ❌ Missing: {$file}\n";
        $azure_missing++;
    }
}

if ($azure_missing > 0) {
    echo "   🔧 FIX: Remove Azure references from install.xml or add missing files\n\n";
}

// 3. Check for HTML entities in PHP files
echo "3. CRITICAL: HTML Entity Corruption in PHP Files\n";
$php_files = glob('**/*.php', GLOB_BRACE);
$corrupted_files = 0;

foreach ($php_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, '&lt;') !== false || strpos($content, '&gt;') !== false) {
            echo "   ❌ HTML entities found in: {$file}\n";
            $corrupted_files++;
        }
    }
}

if ($corrupted_files > 0) {
    echo "   🔧 FIX: Replace &lt; with < and &gt; with > in PHP files\n\n";
}

// 4. Check PHP Requirements
echo "4. PHP Environment Check\n";
echo "   PHP Version: " . PHP_VERSION;
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo " ✅\n";
} else {
    echo " ❌ (Requires 8.0+)\n";
}

$extensions = ['openssl', 'curl', 'json', 'mbstring'];
foreach ($extensions as $ext) {
    echo "   Extension {$ext}: " . (extension_loaded($ext) ? "✅" : "❌") . "\n";
}

echo "\n=== PRIORITY FIXES REQUIRED ===\n";
echo "1. Fix bootstrap.php line 89: registry->get() usage\n";
echo "2. Remove or implement Azure integration\n";
echo "3. Fix HTML entity corruption in PHP files\n";
echo "4. Ensure PHP 8.0+ compatibility\n";

echo "\n=== NEXT STEPS ===\n";
echo "Run this diagnostic after each fix to verify progress.\n";
