<?php
/**
 * Check OpenCart Extension Table Structure
 */

// Database configuration from OpenCart
require_once('opencart_new/config.php');

// Database connection
$connection = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

echo "=== OpenCart Extension Table Structure ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Check table structure
$structure_query = "DESCRIBE " . DB_PREFIX . "extension";
$structure_result = $connection->query($structure_query);

if ($structure_result) {
    echo "Extension table structure:\n";
    while ($row = $structure_result->fetch_assoc()) {
        echo "- {$row['Field']}: {$row['Type']} (Null: {$row['Null']}, Default: " . ($row['Default'] ?? 'NULL') . ")\n";
    }
} else {
    echo "Error getting table structure: " . $connection->error . "\n";
}

// Check existing extensions
echo "\n=== Existing Extensions ===\n";
$existing_query = "SELECT * FROM " . DB_PREFIX . "extension LIMIT 5";
$existing_result = $connection->query($existing_query);

if ($existing_result) {
    while ($row = $existing_result->fetch_assoc()) {
        echo "Sample record:\n";
        foreach ($row as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
        echo "\n";
        break; // Just show one sample
    }
} else {
    echo "Error getting sample data: " . $connection->error . "\n";
}

$connection->close();
?>