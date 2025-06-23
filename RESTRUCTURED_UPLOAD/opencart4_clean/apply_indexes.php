<?php
// Temporary script to apply performance indexes and optimizations

// Load OpenCart config
require_once('config.php');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Database
$db = new \Opencart\System\Library\DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

echo "Database connection established.\n";

// Read the SQL file
$sql_file = DIR_OPENCART . '../sql/performance_indexes.sql';
if (!file_exists($sql_file)) {
    die("ERROR: SQL file not found at " . $sql_file . "\n");
}
$sql_commands = file_get_contents($sql_file);

// Remove comments and split into individual commands
$sql_commands = preg_replace('/--.*/', '', $sql_commands);
$sql_commands = preg_split('/;/', $sql_commands, -1, PREG_SPLIT_NO_EMPTY);

$total_commands = count($sql_commands);
$executed_commands = 0;
$failed_commands = 0;

echo "Found " . $total_commands . " SQL commands to execute.\n\n";

// Execute each command
foreach ($sql_commands as $command) {
    $command = trim($command);
    if (empty($command)) {
        continue;
    }

    try {
        $db->query($command);
        echo "SUCCESS: Executed command:\n" . $command . "\n\n";
        $executed_commands++;
    } catch (Exception $e) {
        echo "FAILED: Could not execute command:\n" . $command . "\n";
        echo "Error: " . $e->getMessage() . "\n\n";
        $failed_commands++;
    }
}

echo "----------------------------------------\n";
echo "Script finished.\n";
echo "Total commands: " . $total_commands . "\n";
echo "Executed successfully: " . $executed_commands . "\n";
echo "Failed: " . $failed_commands . "\n";

?> 