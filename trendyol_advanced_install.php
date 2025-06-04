<?php
/**
 * Trendyol Advanced Features Database Installation Script
 * MesChain-Sync v3.1 - Advanced Features Deployment
 * 
 * This script will:
 * 1. Create the required database tables for Trendyol Advanced features
 * 2. Initialize default settings
 * 3. Verify installation
 * 
 * Usage: Run this script via web browser or command line to install the database tables
 */

// OpenCart Configuration
define('DIR_APPLICATION', __DIR__ . '/upload/admin/');
define('DIR_SYSTEM', __DIR__ . '/upload/system/');
define('DIR_DATABASE', DIR_SYSTEM . 'library/');

// Database configuration - UPDATE THESE WITH YOUR OPENCART DATABASE SETTINGS
$db_config = [
    'hostname' => 'localhost',
    'username' => 'your_db_username',
    'password' => 'your_db_password',
    'database' => 'your_opencart_database',
    'port'     => '3306',
    'prefix'   => 'oc_'  // Your OpenCart table prefix
];

// Define DB_PREFIX for compatibility
define('DB_PREFIX', $db_config['prefix']);

echo "<h1>🚀 Trendyol Advanced Features Installation</h1>\n";
echo "<pre>\n";

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host={$db_config['hostname']};port={$db_config['port']};dbname={$db_config['database']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Database connection established\n";
    
    // Create AI optimization table
    echo "📊 Creating trendyol_ai_optimization table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_ai_optimization` (
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
    echo "✅ AI optimization table created\n";
    
    // Create analytics metrics table
    echo "📈 Creating trendyol_analytics table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_analytics` (
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
    echo "✅ Analytics table created\n";
    
    // Create performance monitoring table
    echo "⚡ Creating trendyol_performance table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_performance` (
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
    echo "✅ Performance monitoring table created\n";
    
    // Create activity log table
    echo "📋 Creating trendyol_activities table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_activities` (
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
    echo "✅ Activities table created\n";
    
    // Create alerts table
    echo "🔔 Creating trendyol_alerts table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_alerts` (
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
    echo "✅ Alerts table created\n";
    
    // Insert some sample data for demonstration
    echo "🎯 Inserting sample data...\n";
    
    // Sample analytics data
    $pdo->exec("
        INSERT IGNORE INTO `" . DB_PREFIX . "trendyol_analytics` 
        (`metric_type`, `metric_value`, `metric_date`, `period_type`, `tenant_id`, `created_at`) VALUES
        ('revenue', 15250.00, CURDATE(), 'daily', 1, NOW()),
        ('orders', 48, CURDATE(), 'daily', 1, NOW()),
        ('products', 1247, CURDATE(), 'daily', 1, NOW()),
        ('conversion', 3.25, CURDATE(), 'daily', 1, NOW()),
        ('performance', 98.7, CURDATE(), 'daily', 1, NOW())
    ");
    
    // Sample activity
    $pdo->exec("
        INSERT INTO `" . DB_PREFIX . "trendyol_activities` 
        (`activity_type`, `description`, `user_id`, `status`, `created_at`, `tenant_id`) VALUES
        ('system', 'Trendyol Advanced features installed successfully', 1, 'success', NOW(), 1)
    ");
    
    echo "✅ Sample data inserted\n";
    
    // Verify installation
    echo "\n🔍 Verifying installation...\n";
    
    $tables = [
        'trendyol_ai_optimization',
        'trendyol_analytics', 
        'trendyol_performance',
        'trendyol_activities',
        'trendyol_alerts'
    ];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
        if ($result->rowCount() > 0) {
            echo "✅ Table {$table} exists\n";
            
            // Check record count
            $count_result = $pdo->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . $table . "`");
            $count = $count_result->fetch()['count'];
            echo "   → {$count} records\n";
        } else {
            echo "❌ Table {$table} missing\n";
        }
    }
    
    echo "\n🎉 Installation completed successfully!\n";
    echo "\n📋 Next Steps:\n";
    echo "1. Access OpenCart Admin Panel\n";
    echo "2. Go to Extensions > Modules > Trendyol Advanced\n";
    echo "3. Configure API settings\n";
    echo "4. Enable advanced features\n";
    echo "5. Start using AI-powered optimization!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Please check your database configuration in this script:\n";
    echo "- hostname: {$db_config['hostname']}\n";
    echo "- database: {$db_config['database']}\n";
    echo "- username: {$db_config['username']}\n";
    echo "- prefix: {$db_config['prefix']}\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n</pre>\n";
?>
