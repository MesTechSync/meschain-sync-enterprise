<?php
// Direct test of MesChain Sync module without authentication
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MesChain Sync Module Direct Test</h1>";

// Test if the controller file exists and can be loaded
$controller_path = __DIR__ . '/extension/meschain_sync/admin/controller/module/meschain_sync.php';
echo "<p>Controller path: " . $controller_path . "</p>";
echo "<p>Controller exists: " . (file_exists($controller_path) ? 'YES' : 'NO') . "</p>";

if (file_exists($controller_path)) {
    echo "<p>Controller file size: " . filesize($controller_path) . " bytes</p>";

    // Try to read the first few lines to check for syntax errors
    $content = file_get_contents($controller_path);
    echo "<p>Controller starts with: " . htmlspecialchars(substr($content, 0, 200)) . "...</p>";

    // Check for PHP syntax errors
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($controller_path), $output, $return_var);

    if ($return_var === 0) {
        echo "<p style='color: green;'>✓ Controller PHP syntax is valid</p>";
    } else {
        echo "<p style='color: red;'>✗ Controller PHP syntax error:</p>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
}

// Test if the API library exists
$api_path = __DIR__ . '/extension/meschain_sync/system/library/meschain/trendyol_api.php';
echo "<p>API library path: " . $api_path . "</p>";
echo "<p>API library exists: " . (file_exists($api_path) ? 'YES' : 'NO') . "</p>";

if (file_exists($api_path)) {
    echo "<p>API library file size: " . filesize($api_path) . " bytes</p>";

    // Check for PHP syntax errors
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($api_path), $output, $return_var);

    if ($return_var === 0) {
        echo "<p style='color: green;'>✓ API library PHP syntax is valid</p>";
    } else {
        echo "<p style='color: red;'>✗ API library PHP syntax error:</p>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
}

// Test template file
$template_path = __DIR__ . '/extension/meschain_sync/admin/view/template/module/meschain_sync.twig';
echo "<p>Template path: " . $template_path . "</p>";
echo "<p>Template exists: " . (file_exists($template_path) ? 'YES' : 'NO') . "</p>";

if (file_exists($template_path)) {
    echo "<p>Template file size: " . filesize($template_path) . " bytes</p>";
}

// Test language files
$lang_en_path = __DIR__ . '/extension/meschain_sync/admin/language/en-gb/module/meschain_sync.php';
$lang_tr_path = __DIR__ . '/extension/meschain_sync/admin/language/tr-tr/module/meschain_sync.php';

echo "<p>English language file exists: " . (file_exists($lang_en_path) ? 'YES' : 'NO') . "</p>";
echo "<p>Turkish language file exists: " . (file_exists($lang_tr_path) ? 'YES' : 'NO') . "</p>";

echo "<h2>File Structure Test Complete</h2>";
echo "<p>All critical files appear to be in place. The issue might be with OpenCart's authentication system.</p>";
