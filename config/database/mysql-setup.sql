-- OpenCart Multi-Port Database Setup
-- This script creates databases and users for both OpenCart instances

-- Create databases
CREATE DATABASE IF NOT EXISTS `opencart_8080` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `opencart_8090` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create users
CREATE USER IF NOT EXISTS 'opencart_8080'@'localhost' IDENTIFIED BY 'oc8080_secure_password_2025';
CREATE USER IF NOT EXISTS 'opencart_8090'@'localhost' IDENTIFIED BY 'oc8090_secure_password_2025';

-- Grant privileges
GRANT ALL PRIVILEGES ON `opencart_8080`.* TO 'opencart_8080'@'localhost';
GRANT ALL PRIVILEGES ON `opencart_8090`.* TO 'opencart_8090'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Show created databases
SHOW DATABASES LIKE 'opencart_%';

-- Show created users
SELECT User, Host FROM mysql.user WHERE User LIKE 'opencart_%';

-- Create initial configuration tables for MesChain Trendyol integration
USE `opencart_8080`;

CREATE TABLE IF NOT EXISTS `oc8080_meschain_trendyol_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `trendyol_product_id` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `price` decimal(15,4) DEFAULT 0.0000,
  `sale_price` decimal(15,4) DEFAULT 0.0000,
  `status` tinyint(1) DEFAULT 1,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `trendyol_product_id` (`trendyol_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oc8080_meschain_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `trendyol_order_id` varchar(255) NOT NULL,
  `shipment_package_id` varchar(255) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `cargo_tracking_number` varchar(255) DEFAULT NULL,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `trendyol_order_id` (`trendyol_order_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oc8080_meschain_trendyol_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings for port 8080 (integrated system)
INSERT INTO `oc8080_meschain_trendyol_settings` (`setting_key`, `setting_value`) VALUES
('api_key', ''),
('api_secret', ''),
('supplier_id', ''),
('environment', 'production'),
('auto_sync', '1'),
('sync_interval', '300'),
('debug_mode', '0'),
('last_sync', ''),
('integration_status', 'enabled');

-- Setup for port 8090 (clean system)
USE `opencart_8090`;

CREATE TABLE IF NOT EXISTS `oc8090_meschain_trendyol_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `trendyol_product_id` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `price` decimal(15,4) DEFAULT 0.0000,
  `sale_price` decimal(15,4) DEFAULT 0.0000,
  `status` tinyint(1) DEFAULT 1,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `trendyol_product_id` (`trendyol_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oc8090_meschain_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `trendyol_order_id` varchar(255) NOT NULL,
  `shipment_package_id` varchar(255) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `cargo_tracking_number` varchar(255) DEFAULT NULL,
  `sync_status` enum('pending','synced','error') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `trendyol_order_id` (`trendyol_order_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oc8090_meschain_trendyol_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings for port 8090 (testing system)
INSERT INTO `oc8090_meschain_trendyol_settings` (`setting_key`, `setting_value`) VALUES
('api_key', ''),
('api_secret', ''),
('supplier_id', ''),
('environment', 'sandbox'),
('auto_sync', '0'),
('sync_interval', '600'),
('debug_mode', '1'),
('last_sync', ''),
('integration_status', 'testing');

-- Create indexes for better performance
USE `opencart_8080`;
CREATE INDEX IF NOT EXISTS `idx_meschain_products_sync` ON `oc8080_meschain_trendyol_products` (`sync_status`, `last_sync`);
CREATE INDEX IF NOT EXISTS `idx_meschain_orders_sync` ON `oc8080_meschain_trendyol_orders` (`sync_status`, `last_sync`);

USE `opencart_8090`;
CREATE INDEX IF NOT EXISTS `idx_meschain_products_sync` ON `oc8090_meschain_trendyol_products` (`sync_status`, `last_sync`);
CREATE INDEX IF NOT EXISTS `idx_meschain_orders_sync` ON `oc8090_meschain_trendyol_orders` (`sync_status`, `last_sync`);

-- Show table status
USE `opencart_8080`;
SHOW TABLES LIKE '%meschain%';

USE `opencart_8090`;
SHOW TABLES LIKE '%meschain%';

SELECT 'Database setup completed successfully!' as Status;
