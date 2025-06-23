-- MesChain-Sync Enterprise Installation SQL
-- Version: 3.0.0
-- Date: 2025-06-19

-- Core Tables
CREATE TABLE IF NOT EXISTS `PREFIX_meschain_marketplace` (
    `marketplace_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `code` VARCHAR(32) NOT NULL,
    `type` VARCHAR(32) NOT NULL,
    `settings` TEXT NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT '0',
    `sort_order` INT(3) NOT NULL DEFAULT '0',
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`marketplace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_meschain_product` (
    `meschain_product_id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `marketplace_id` INT(11) NOT NULL,
    `marketplace_product_id` VARCHAR(128) NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT '0',
    `sync_status` VARCHAR(32) NOT NULL,
    `last_sync` DATETIME NOT NULL,
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`meschain_product_id`),
    KEY `product_id` (`product_id`),
    KEY `marketplace_id` (`marketplace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_meschain_order` (
    `meschain_order_id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `marketplace_id` INT(11) NOT NULL,
    `marketplace_order_id` VARCHAR(128) NOT NULL,
    `status` VARCHAR(32) NOT NULL,
    `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    `currency_code` VARCHAR(3) NOT NULL,
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`meschain_order_id`),
    KEY `order_id` (`order_id`),
    KEY `marketplace_id` (`marketplace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Azure Integration Tables
CREATE TABLE IF NOT EXISTS `PREFIX_meschain_azure_config` (
    `config_id` INT(11) NOT NULL AUTO_INCREMENT,
    `service_name` VARCHAR(64) NOT NULL,
    `settings` TEXT NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT '0',
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`config_id`),
    UNIQUE KEY `service_name` (`service_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_meschain_azure_log` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT,
    `service_name` VARCHAR(64) NOT NULL,
    `event_type` VARCHAR(32) NOT NULL,
    `message` TEXT NOT NULL,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`log_id`),
    KEY `service_name` (`service_name`),
    KEY `event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Performance Optimization Indexes
CREATE INDEX `idx_meschain_product_sync` ON `PREFIX_meschain_product` (`sync_status`, `last_sync`);
CREATE INDEX `idx_meschain_order_status` ON `PREFIX_meschain_order` (`status`, `date_modified`);
CREATE INDEX `idx_azure_log_date` ON `PREFIX_meschain_azure_log` (`date_added`);

-- Default Data
INSERT INTO `PREFIX_meschain_marketplace` 
(`name`, `code`, `type`, `settings`, `status`, `sort_order`, `date_added`, `date_modified`) VALUES
('Amazon', 'amazon', 'marketplace', '{"api_key":"","api_secret":"","region":""}', 0, 1, NOW(), NOW()),
('Trendyol', 'trendyol', 'marketplace', '{"api_key":"","api_secret":""}', 0, 2, NOW(), NOW()),
('N11', 'n11', 'marketplace', '{"api_key":"","api_secret":""}', 0, 3, NOW(), NOW()),
('Hepsiburada', 'hepsiburada', 'marketplace', '{"api_key":"","api_secret":""}', 0, 4, NOW(), NOW()),
('eBay', 'ebay', 'marketplace', '{"api_key":"","api_secret":""}', 0, 5, NOW(), NOW()),
('GittiGidiyor', 'gittigidiyor', 'marketplace', '{"api_key":"","api_secret":""}', 0, 6, NOW(), NOW()),
('PttAVM', 'pttavm', 'marketplace', '{"api_key":"","api_secret":""}', 0, 7, NOW(), NOW());

-- Azure Service Configuration
INSERT INTO `PREFIX_meschain_azure_config` 
(`service_name`, `settings`, `status`, `date_added`, `date_modified`) VALUES
('Azure Blob Storage', '{"connection_string":"","container_name":""}', 0, NOW(), NOW()),
('Azure Queue Storage', '{"connection_string":"","queue_name":""}', 0, NOW(), NOW()),
('Azure Application Insights', '{"instrumentation_key":""}', 0, NOW(), NOW()),
('Azure Key Vault', '{"tenant_id":"","client_id":"","client_secret":""}', 0, NOW(), NOW());
