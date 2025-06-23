<?php
/**
 * MesChain-Sync Enterprise Database Installation Script
 * Comprehensive database setup for OpenCart 4.0.2.3
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🚀 ============================================\n";
echo "🚀 MesChain-Sync Enterprise Database Setup\n";
echo "🚀 ============================================\n\n";

// Database configuration
$db_config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '1234',
    'database' => 'opencart4',
    'prefix' => 'oc_'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['hostname']};dbname={$db_config['database']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "📊 1. Creating MesChain Core Tables...\n";

// 1. MesChain Marketplace Configuration Table
$sql_marketplace = "CREATE TABLE IF NOT EXISTS `{$db_config['prefix']}meschain_marketplaces` (
    `marketplace_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `code` varchar(50) NOT NULL UNIQUE,
    `api_endpoint` varchar(255),
    `api_key` varchar(255),
    `api_secret` varchar(255),
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `commission_rate` decimal(5,2) DEFAULT '0.00',
    `sync_products` tinyint(1) DEFAULT '1',
    `sync_orders` tinyint(1) DEFAULT '1',
    `sync_inventory` tinyint(1) DEFAULT '1',
    `last_sync` datetime NULL,
    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`marketplace_id`),
    KEY `code` (`code`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $pdo->exec($sql_marketplace);
    echo "✅ Created oc_meschain_marketplaces table\n";
} catch (PDOException $e) {
    echo "⚠️  Error creating marketplaces table: " . $e->getMessage() . "\n";
}

// 2. MesChain Product Sync Table
$sql_products = "CREATE TABLE IF NOT EXISTS `{$db_config['prefix']}meschain_products` (
    `meschain_product_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `marketplace_id` int(11) NOT NULL,
    `marketplace_product_id` varchar(100),
    `marketplace_sku` varchar(100),
    `marketplace_url` text,
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `sync_status` enum('pending','syncing','synced','error','disabled') DEFAULT 'pending',
    `price_margin` decimal(15,4) DEFAULT '0.0000',
    `profit_margin` decimal(5,2) DEFAULT '10.00',
    `marketplace_price` decimal(15,4) DEFAULT '0.0000',
    `marketplace_stock` int(11) DEFAULT '0',
    `last_sync` datetime NULL,
    `error_message` text,
    `sync_attempts` int(11) DEFAULT '0',
    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`meschain_product_id`),
    KEY `product_id` (`product_id`),
    KEY `marketplace_id` (`marketplace_id`),
    KEY `sync_status` (`sync_status`),
    KEY `marketplace_sku` (`marketplace_sku`),
    UNIQUE KEY `product_marketplace` (`product_id`, `marketplace_id`),
    FOREIGN KEY (`marketplace_id`) REFERENCES `{$db_config['prefix']}meschain_marketplaces`(`marketplace_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $pdo->exec($sql_products);
    echo "✅ Created oc_meschain_products table\n";
} catch (PDOException $e) {
    echo "⚠️  Error creating products table: " . $e->getMessage() . "\n";
}

// 3. MesChain Order Sync Table
$sql_orders = "CREATE TABLE IF NOT EXISTS `{$db_config['prefix']}meschain_orders` (
    `meschain_order_id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NULL,
    `marketplace_id` int(11) NOT NULL,
    `marketplace_order_id` varchar(100) NOT NULL,
    `marketplace_status` varchar(50),
    `commission_rate` decimal(5,2) DEFAULT '0.00',
    `commission_amount` decimal(15,4) DEFAULT '0.0000',
    `shipping_cost` decimal(15,4) DEFAULT '0.0000',
    `total_amount` decimal(15,4) DEFAULT '0.0000',
    `sync_status` enum('pending','imported','synced','error','cancelled') DEFAULT 'pending',
    `customer_name` varchar(255),
    `customer_email` varchar(255),
    `shipping_address` text,
    `order_date` datetime,
    `last_sync` datetime NULL,
    `error_message` text,
    `raw_data` longtext,
    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`meschain_order_id`),
    KEY `order_id` (`order_id`),
    KEY `marketplace_id` (`marketplace_id`),
    KEY `marketplace_order_id` (`marketplace_order_id`),
    KEY `sync_status` (`sync_status`),
    UNIQUE KEY `marketplace_order_unique` (`marketplace_id`, `marketplace_order_id`),
    FOREIGN KEY (`marketplace_id`) REFERENCES `{$db_config['prefix']}meschain_marketplaces`(`marketplace_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $pdo->exec($sql_orders);
    echo "✅ Created oc_meschain_orders table\n";
} catch (PDOException $e) {
    echo "⚠️  Error creating orders table: " . $e->getMessage() . "\n";
}

// 4. MesChain Sync Logs Table
$sql_logs = "CREATE TABLE IF NOT EXISTS `{$db_config['prefix']}meschain_logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `level` enum('debug','info','warning','error','critical') DEFAULT 'info',
    `message` text NOT NULL,
    `context` longtext,
    `marketplace` varchar(50),
    `entity_type` varchar(50),
    `entity_id` varchar(100),
    `user_id` int(11) NULL,
    `ip_address` varchar(45),
    `user_agent` text,
    `execution_time` decimal(8,4) DEFAULT NULL,
    `memory_usage` int(11) DEFAULT NULL,
    `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `level` (`level`),
    KEY `marketplace` (`marketplace`),
    KEY `entity_type` (`entity_type`),
    KEY `date_added` (`date_added`),
    KEY `level_date` (`level`, `date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $pdo->exec($sql_logs);
    echo "✅ Created oc_meschain_logs table\n";
} catch (PDOException $e) {
    echo "⚠️  Error creating logs table: " . $e->getMessage() . "\n";
}

echo "\n📊 2. Inserting Default Marketplace Configurations...\n";

// Insert default marketplaces
$marketplaces = [
    ['name' => 'Trendyol', 'code' => 'trendyol', 'api_endpoint' => 'https://api.trendyol.com', 'commission_rate' => 12.50],
    ['name' => 'Hepsiburada', 'code' => 'hepsiburada', 'api_endpoint' => 'https://api.hepsiburada.com', 'commission_rate' => 15.00],
    ['name' => 'Amazon TR', 'code' => 'amazon_tr', 'api_endpoint' => 'https://sellingpartnerapi-eu.amazon.com', 'commission_rate' => 18.00],
    ['name' => 'N11', 'code' => 'n11', 'api_endpoint' => 'https://api.n11.com', 'commission_rate' => 10.00],
    ['name' => 'eBay', 'code' => 'ebay', 'api_endpoint' => 'https://api.ebay.com', 'commission_rate' => 13.00],
    ['name' => 'GittiGidiyor', 'code' => 'gittigidiyor', 'api_endpoint' => 'https://dev.gittigidiyor.com', 'commission_rate' => 8.50],
    ['name' => 'Pazarama', 'code' => 'pazarama', 'api_endpoint' => 'https://isortagimapi.pazarama.com', 'commission_rate' => 7.00]
];

$stmt = $pdo->prepare("INSERT INTO `{$db_config['prefix']}meschain_marketplaces` 
    (name, code, api_endpoint, commission_rate, status) 
    VALUES (?, ?, ?, ?, 1) 
    ON DUPLICATE KEY UPDATE 
    name = VALUES(name), 
    api_endpoint = VALUES(api_endpoint), 
    commission_rate = VALUES(commission_rate)");

foreach ($marketplaces as $marketplace) {
    try {
        $stmt->execute([$marketplace['name'], $marketplace['code'], $marketplace['api_endpoint'], $marketplace['commission_rate']]);
        echo "✅ Configured marketplace: {$marketplace['name']}\n";
    } catch (PDOException $e) {
        echo "⚠️  Error configuring {$marketplace['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 3. Setting Up OpenCart Extension Configuration...\n";

// Insert extension settings
$settings = [
    'module_meschain_sync_status' => '1',
    'module_meschain_sync_name' => 'MesChain-Sync Enterprise',
    'module_meschain_sync_version' => '3.0.0',
    'module_meschain_sync_auto_sync' => '1',
    'module_meschain_sync_sync_interval' => '300', // 5 minutes
    'module_meschain_sync_debug_mode' => '1',
    'module_meschain_sync_log_level' => 'info',
    'module_meschain_sync_max_retries' => '3',
    'module_meschain_sync_timeout' => '30',
    'module_meschain_sync_batch_size' => '50'
];

$stmt = $pdo->prepare("INSERT INTO `{$db_config['prefix']}setting` 
    (store_id, `code`, `key`, `value`, serialized) 
    VALUES (0, 'module_meschain_sync', ?, ?, 0) 
    ON DUPLICATE KEY UPDATE 
    `value` = VALUES(`value`)");

foreach ($settings as $key => $value) {
    try {
        $stmt->execute([$key, $value]);
        echo "✅ Set configuration: {$key} = {$value}\n";
    } catch (PDOException $e) {
        echo "⚠️  Error setting {$key}: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 4. Creating Database Indexes for Performance...\n";

// Additional performance indexes
$indexes = [
    "CREATE INDEX idx_meschain_products_status ON `{$db_config['prefix']}meschain_products` (status, sync_status)",
    "CREATE INDEX idx_meschain_orders_date ON `{$db_config['prefix']}meschain_orders` (order_date, sync_status)",
    "CREATE INDEX idx_meschain_logs_cleanup ON `{$db_config['prefix']}meschain_logs` (date_added, level)"
];

foreach ($indexes as $index_sql) {
    try {
        $pdo->exec($index_sql);
        echo "✅ Created performance index\n";
    } catch (PDOException $e) {
        // Index might already exist, that's okay
        if (strpos($e->getMessage(), 'Duplicate key name') === false) {
            echo "⚠️  Index creation note: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n📊 5. Database Status Summary...\n";

// Check table status
$tables = ['meschain_marketplaces', 'meschain_products', 'meschain_orders', 'meschain_logs'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$db_config['prefix']}{$table}`");
        $result = $stmt->fetch();
        echo "✅ Table {$db_config['prefix']}{$table}: {$result['count']} records\n";
    } catch (PDOException $e) {
        echo "❌ Table {$db_config['prefix']}{$table}: Error - " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 ============================================\n";
echo "🎉 MesChain Database Setup COMPLETED!\n";
echo "🎉 ============================================\n\n";

echo "📋 Next Steps:\n";
echo "1. Test database connection: php test_meschain_sync.php\n";
echo "2. Access admin panel: http://localhost:8080/admin/\n";
echo "3. Navigate to Extensions → Modules → MesChain-Sync\n";
echo "4. Configure marketplace API credentials\n\n";

echo "🔧 Database is ready for MesChain-Sync Enterprise! 🔧\n";
?> 