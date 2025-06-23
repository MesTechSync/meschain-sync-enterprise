<?php
// Check actual table structure
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'opencart_new';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Database Table Structure Analysis</h2>\n";
    
    // Check oc_extension_path structure
    echo "<h3>📋 oc_extension_path Table Structure:</h3>\n";
    $stmt = $pdo->query("DESCRIBE oc_extension_path");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Check oc_extension structure
    echo "<h3>📋 oc_extension Table Structure:</h3>\n";
    $stmt = $pdo->query("DESCRIBE oc_extension");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Check oc_user_group structure
    echo "<h3>📋 oc_user_group Table Structure:</h3>\n";
    $stmt = $pdo->query("DESCRIBE oc_user_group");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Field</th><th>Type</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Sample data from oc_extension_path
    echo "<h3>📋 Sample data from oc_extension_path:</h3>\n";
    $stmt = $pdo->query("SELECT * FROM oc_extension_path LIMIT 10");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($rows)) {
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr>";
        foreach (array_keys($rows[0]) as $column) {
            echo "<th>{$column}</th>";
        }
        echo "</tr>\n";
        
        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>{$value}</td>";
            }
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>