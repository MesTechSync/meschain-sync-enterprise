<?php
/**
 * Trendyol Advanced Installation API
 * Handles database installation via AJAX
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get database configuration from form
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_prefix = $_POST['db_prefix'] ?? 'oc_';
    
    if (empty($db_name) || empty($db_user)) {
        throw new Exception('Database name and username are required');
    }
    
    // Create database connection
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $tables_created = [];
    
    // Create AI optimization table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$db_prefix}trendyol_ai_optimization` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `optimization_type` enum('price','description','category','keywords') NOT NULL,
            `original_value` text,
            `optimized_value` text,
            `confidence_score` decimal(5,2) DEFAULT NULL,
            `performance_impact` decimal(5,2) DEFAULT NULL,
            `applied` tinyint(1) DEFAULT 0,
            `date_created` datetime NOT NULL,
            `date_applied` datetime DEFAULT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `product_id` (`product_id`),
            KEY `optimization_type` (`optimization_type`),
            KEY `tenant_id` (`tenant_id`),
            KEY `date_created` (`date_created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $tables_created[] = "{$db_prefix}trendyol_ai_optimization";
    
    // Create analytics metrics table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$db_prefix}trendyol_analytics` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `metric_type` enum('revenue','orders','products','conversion','performance') NOT NULL,
            `metric_value` decimal(15,2) NOT NULL,
            `metric_date` date NOT NULL,
            `period_type` enum('daily','weekly','monthly','yearly') NOT NULL,
            `additional_data` json DEFAULT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_metric` (`metric_type`,`metric_date`,`period_type`,`tenant_id`),
            KEY `metric_type` (`metric_type`),
            KEY `metric_date` (`metric_date`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $tables_created[] = "{$db_prefix}trendyol_analytics";
    
    // Create performance monitoring table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$db_prefix}trendyol_performance` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `endpoint` varchar(255) NOT NULL,
            `response_time` int(11) NOT NULL COMMENT 'milliseconds',
            `status_code` int(3) NOT NULL,
            `error_message` text DEFAULT NULL,
            `request_size` int(11) DEFAULT NULL,
            `response_size` int(11) DEFAULT NULL,
            `timestamp` datetime NOT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `endpoint` (`endpoint`),
            KEY `timestamp` (`timestamp`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $tables_created[] = "{$db_prefix}trendyol_performance";
    
    // Create activity log table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$db_prefix}trendyol_activities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `activity_type` varchar(50) NOT NULL,
            `description` text NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `affected_items` json DEFAULT NULL,
            `status` enum('success','warning','error') NOT NULL,
            `created_at` datetime NOT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `activity_type` (`activity_type`),
            KEY `user_id` (`user_id`),
            KEY `created_at` (`created_at`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $tables_created[] = "{$db_prefix}trendyol_activities";
    
    // Create alerts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$db_prefix}trendyol_alerts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `alert_type` enum('stock','price','performance','api','system') NOT NULL,
            `severity` enum('low','medium','high','critical') NOT NULL,
            `title` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `is_read` tinyint(1) DEFAULT 0,
            `is_resolved` tinyint(1) DEFAULT 0,
            `created_at` datetime NOT NULL,
            `resolved_at` datetime DEFAULT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `alert_type` (`alert_type`),
            KEY `severity` (`severity`),
            KEY `is_read` (`is_read`),
            KEY `created_at` (`created_at`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $tables_created[] = "{$db_prefix}trendyol_alerts";
    
    // Insert sample data
    $pdo->exec("
        INSERT IGNORE INTO `{$db_prefix}trendyol_analytics` 
        (`metric_type`, `metric_value`, `metric_date`, `period_type`, `tenant_id`, `created_at`) VALUES
        ('revenue', 15250.00, CURDATE(), 'daily', 1, NOW()),
        ('orders', 48, CURDATE(), 'daily', 1, NOW()),
        ('products', 1247, CURDATE(), 'daily', 1, NOW()),
        ('conversion', 3.25, CURDATE(), 'daily', 1, NOW()),
        ('performance', 98.7, CURDATE(), 'daily', 1, NOW())
    ");
    
    $pdo->exec("
        INSERT INTO `{$db_prefix}trendyol_activities` 
        (`activity_type`, `description`, `user_id`, `status`, `created_at`, `tenant_id`) VALUES
        ('system', 'Trendyol Advanced features installed via web installer', 1, 'success', NOW(), 1)
    ");
    
    // Verify installation
    $verified_tables = [];
    foreach ($tables_created as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($result->rowCount() > 0) {
            $verified_tables[] = $table;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Trendyol Advanced features installed successfully!',
        'tables' => $verified_tables,
        'count' => count($verified_tables)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Installation failed',
        'error' => $e->getMessage()
    ]);
}
?>
