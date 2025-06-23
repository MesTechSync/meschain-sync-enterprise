<?php
// Standalone script to create all MesChain tables

// Database configuration
$config = [
    'hostname' => 'localhost',
    'username' => 'opencart4_user',
    'password' => '1234',
    'database' => 'opencart4',
    'port' => '3306',
    'prefix' => 'oc_'
];

// Connect to database
$mysqli = new mysqli(
    $config['hostname'], 
    $config['username'], 
    $config['password'], 
    $config['database'], 
    $config['port']
);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database successfully\n\n";

$prefix = $config['prefix'];

// Create base MesChain tables
$tables = [
    // meschain_settings table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_settings` (
        `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
        `marketplace` VARCHAR(255) NOT NULL,
        `key` VARCHAR(255) NOT NULL,
        `value` TEXT NOT NULL,
        `serialized` TINYINT(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`setting_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_product_sync table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_product_sync` (
        `sync_id` INT(11) NOT NULL AUTO_INCREMENT,
        `product_id` INT(11) NOT NULL,
        `marketplace` VARCHAR(255) NOT NULL,
        `marketplace_product_id` VARCHAR(255) NOT NULL,
        `last_sync` DATETIME NOT NULL,
        `sync_status` VARCHAR(50) NOT NULL,
        `error_message` TEXT,
        PRIMARY KEY (`sync_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_order_integration table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_order_integration` (
        `integration_id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `marketplace` VARCHAR(255) NOT NULL,
        `marketplace_order_id` VARCHAR(255) NOT NULL,
        `import_date` DATETIME NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        PRIMARY KEY (`integration_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_logs table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_logs` (
        `log_id` INT(11) NOT NULL AUTO_INCREMENT,
        `marketplace` VARCHAR(255) NOT NULL,
        `log_level` VARCHAR(50) NOT NULL,
        `message` TEXT NOT NULL,
        `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_metrics table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_metrics` (
        `metric_id` INT(11) NOT NULL AUTO_INCREMENT,
        `metric_name` VARCHAR(255) NOT NULL,
        `value` DECIMAL(15,4) NOT NULL,
        `marketplace` VARCHAR(255) DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`metric_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_category_mapping table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_category_mapping` (
        `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
        `opencart_category_id` INT(11) NOT NULL,
        `marketplace` VARCHAR(255) NOT NULL,
        `marketplace_category_id` VARCHAR(255) NOT NULL,
        `marketplace_category_name` TEXT NOT NULL,
        PRIMARY KEY (`mapping_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_api_cache table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_api_cache` (
        `cache_id` INT(11) NOT NULL AUTO_INCREMENT,
        `cache_key` VARCHAR(255) NOT NULL,
        `value` LONGTEXT NOT NULL,
        `expires_at` DATETIME NOT NULL,
        PRIMARY KEY (`cache_id`),
        UNIQUE KEY `cache_key` (`cache_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_product_mapping table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_product_mapping` (
        `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
        `product_id` INT(11) NOT NULL,
        `marketplace` VARCHAR(50) NOT NULL,
        `marketplace_product_id` VARCHAR(255) NOT NULL,
        `date_added` DATETIME NOT NULL,
        `date_modified` DATETIME DEFAULT NULL,
        PRIMARY KEY (`mapping_id`),
        UNIQUE KEY `product_marketplace` (`product_id`, `marketplace`, `marketplace_product_id`),
        KEY `marketplace` (`marketplace`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_order_mapping table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_order_mapping` (
        `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `marketplace` VARCHAR(50) NOT NULL,
        `marketplace_order_id` VARCHAR(255) NOT NULL,
        `date_added` DATETIME NOT NULL,
        PRIMARY KEY (`mapping_id`),
        UNIQUE KEY `order_marketplace` (`order_id`, `marketplace`, `marketplace_order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_activity_log table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_activity_log` (
        `log_id` INT(11) NOT NULL AUTO_INCREMENT,
        `marketplace` VARCHAR(50) NOT NULL,
        `activity_type` VARCHAR(100) NOT NULL,
        `message` TEXT NOT NULL,
        `date_added` DATETIME NOT NULL,
        PRIMARY KEY (`log_id`),
        KEY `marketplace` (`marketplace`),
        KEY `activity_type` (`activity_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_trendyol_products table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_trendyol_products` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `product_id` INT(11) NOT NULL,
        `barcode` VARCHAR(255) NOT NULL,
        `stock_code` VARCHAR(255) NOT NULL,
        `title` VARCHAR(500) NOT NULL,
        `description` TEXT,
        `brand_id` INT(11),
        `category_id` INT(11),
        `quantity` INT(11) DEFAULT '0',
        `list_price` DECIMAL(15,2),
        `sale_price` DECIMAL(15,2),
        `vat_rate` INT(11) DEFAULT '18',
        `cargo_company_id` INT(11),
        `images` TEXT,
        `attributes` TEXT,
        `sync_status` VARCHAR(50) DEFAULT 'pending',
        `sync_message` TEXT,
        `date_added` DATETIME NOT NULL,
        `date_modified` DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `barcode` (`barcode`),
        KEY `product_id` (`product_id`),
        KEY `sync_status` (`sync_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_trendyol_orders table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_trendyol_orders` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `order_number` VARCHAR(255) NOT NULL,
        `status` VARCHAR(50) NOT NULL,
        `payment_type` VARCHAR(50),
        `shipment_package_status` VARCHAR(50),
        `invoice_address` TEXT,
        `shipment_address` TEXT,
        `customer_email` VARCHAR(255),
        `customer_first_name` VARCHAR(255),
        `customer_last_name` VARCHAR(255),
        `gross_amount` DECIMAL(15,2),
        `total_discount` DECIMAL(15,2),
        `total_price` DECIMAL(15,2),
        `lines` TEXT,
        `cargo_tracking_number` VARCHAR(255),
        `cargo_provider_name` VARCHAR(255),
        `date_created` DATETIME NOT NULL,
        `date_modified` DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_number` (`order_number`),
        KEY `order_id` (`order_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_trendyol_categories table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_trendyol_categories` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `category_id` INT(11) NOT NULL,
        `parent_id` INT(11) DEFAULT '0',
        `name` VARCHAR(500) NOT NULL,
        `path` VARCHAR(1000),
        `commission` DECIMAL(5,2),
        `date_added` DATETIME NOT NULL,
        `date_modified` DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `category_id` (`category_id`),
        KEY `parent_id` (`parent_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // meschain_trendyol_logs table
    "CREATE TABLE IF NOT EXISTS `{$prefix}meschain_trendyol_logs` (
        `log_id` INT(11) NOT NULL AUTO_INCREMENT,
        `type` VARCHAR(50) NOT NULL,
        `endpoint` VARCHAR(255),
        `request` TEXT,
        `response` TEXT,
        `status_code` INT(11),
        `error_message` TEXT,
        `date_added` DATETIME NOT NULL,
        PRIMARY KEY (`log_id`),
        KEY `type` (`type`),
        KEY `date_added` (`date_added`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// Execute each table creation query
foreach ($tables as $query) {
    if ($mysqli->query($query)) {
        // Extract table name from query for display
        preg_match('/CREATE TABLE IF NOT EXISTS `([^`]+)`/', $query, $matches);
        echo "✓ Created table: " . $matches[1] . "\n";
    } else {
        echo "✗ Error creating table: " . $mysqli->error . "\n";
    }
}

// Insert default Trendyol settings if not exists
$check = $mysqli->query("SELECT * FROM {$prefix}meschain_settings WHERE marketplace = 'trendyol'");
if ($check->num_rows == 0) {
    echo "\nInserting default Trendyol settings...\n";
    
    $settings = [
        ['trendyol', 'api_key', 'f4KhSfv7ihjXcJFlJeim', 0],
        ['trendyol', 'api_secret', 'GLs2YLpJwPJtEX6dSPbi', 0],
        ['trendyol', 'supplier_id', '1076956', 0],
        ['trendyol', 'enabled', '1', 0],
        ['trendyol', 'sync_interval', '30', 0],
        ['trendyol', 'auto_sync', '0', 0]
    ];
    
    foreach ($settings as $setting) {
        $stmt = $mysqli->prepare("INSERT INTO {$prefix}meschain_settings (marketplace, `key`, value, serialized) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $setting[0], $setting[1], $setting[2], $setting[3]);
        if ($stmt->execute()) {
            echo "✓ Added setting: {$setting[1]}\n";
        }
        $stmt->close();
    }
}

echo "\n✅ All MesChain tables have been created successfully!\n";

$mysqli->close();
?> 