<?php
// OpenCart Extension Discovery Debug Script
require_once('./opencart_new/config.php');

echo "=== OPENCART EXTENSION DISCOVERY DEBUG ===\n\n";

// Database connection
$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "1. CHECKING EXTENSION PATHS TABLE:\n";
echo "=================================\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%/admin/controller/module/%'");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Path: " . $row['path'] . " | Install ID: " . $row['extension_install_id'] . "\n";
    }
} else {
    echo "NO EXTENSION PATHS FOUND!\n";
}

echo "\n2. CHECKING EXTENSION TABLE:\n";
echo "===========================\n";
$result = $mysqli->query("SELECT * FROM " . DB_PREFIX . "extension WHERE type = 'module'");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Extension: " . $row['extension'] . " | Type: " . $row['type'] . " | Code: " . $row['code'] . "\n";
    }
} else {
    echo "NO MODULE EXTENSIONS FOUND!\n";
}

echo "\n3. PHYSICAL FILE STRUCTURE CHECK:\n";
echo "=================================\n";
$module_dir = './opencart_new/admin/controller/extension/module/';
$files = glob($module_dir . 'meschain_*.php');
echo "MesChain module files found:\n";
foreach ($files as $file) {
    $filename = basename($file, '.php');
    echo "- $filename\n";
}

echo "\n4. OPENCART 4.x EXTENSION DIRECTORY CHECK:\n";
echo "=========================================\n";
$extension_base = './opencart_new/extension/';
if (is_dir($extension_base)) {
    $extensions = glob($extension_base . '*', GLOB_ONLYDIR);
    echo "Extension directories found:\n";
    foreach ($extensions as $ext) {
        echo "- " . basename($ext) . "\n";
    }
} else {
    echo "Extension directory does not exist: $extension_base\n";
}

echo "\n5. CRITICAL ANALYSIS:\n";
echo "====================\n";

// Check if the discovery method works
$model_extension_simulation = "SELECT * FROM " . DB_PREFIX . "extension_path WHERE path LIKE '%/admin/controller/module/%.php'";
$paths_result = $mysqli->query($model_extension_simulation);
echo "Extensions discoverable by OpenCart mechanism: " . $paths_result->num_rows . "\n";

// Check if modules exist in old structure but not registered
$old_structure_files = glob('./opencart_new/admin/controller/extension/module/meschain_*.php');
echo "MesChain modules in old structure: " . count($old_structure_files) . "\n";

echo "\n6. PROBLEM DIAGNOSIS:\n";
echo "====================\n";

if ($paths_result->num_rows == 0) {
    echo "❌ CRITICAL: No extension paths registered in database\n";
    echo "   OpenCart cannot discover extensions without extension_path entries\n";
}

if (count($old_structure_files) > 0 && $paths_result->num_rows == 0) {
    echo "❌ CRITICAL: Extensions exist in files but not registered in database\n";
    echo "   Extensions are using OpenCart 3.x structure but OpenCart 4.x discovery method\n";
}

$mysqli->close();
?>