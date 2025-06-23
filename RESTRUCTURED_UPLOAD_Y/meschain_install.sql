-- MesChain-Sync Enterprise SQL Kurulum Dosyası
-- Version: 3.0.0
-- Tarih: 19 Haziran 2025

-- Pazaryeri Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_marketplace` (
    `marketplace_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64) NOT NULL,
    `code` VARCHAR(32) NOT NULL,
    `settings` TEXT NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT '0',
    `sort_order` INT(3) NOT NULL DEFAULT '0',
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`marketplace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ürün Entegrasyon Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_product` (
    `meschain_product_id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `marketplace_id` INT(11) NOT NULL,
    `marketplace_product_id` VARCHAR(128) NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT '0',
    `price` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    `quantity` INT(4) NOT NULL DEFAULT '0',
    `profit_margin` DECIMAL(5,2) NOT NULL DEFAULT '10.00',
    `sync_status` VARCHAR(32) NOT NULL,
    `last_sync` DATETIME NOT NULL,
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`meschain_product_id`),
    KEY `product_id` (`product_id`),
    KEY `marketplace_id` (`marketplace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sipariş Entegrasyon Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_order` (
    `meschain_order_id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `marketplace_id` INT(11) NOT NULL,
    `marketplace_order_id` VARCHAR(128) NOT NULL,
    `status` VARCHAR(32) NOT NULL,
    `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    `commission` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    `currency_code` VARCHAR(3) NOT NULL,
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`meschain_order_id`),
    KEY `order_id` (`order_id`),
    KEY `marketplace_id` (`marketplace_id`),
    KEY `marketplace_order_id` (`marketplace_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Log Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_log` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(32) NOT NULL,
    `reference_id` INT(11) NOT NULL,
    `message` TEXT NOT NULL,
    `date_added` DATETIME NOT NULL,
    PRIMARY KEY (`log_id`),
    KEY `type` (`type`),
    KEY `reference_id` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Varsayılan Pazaryeri Verileri
INSERT INTO `oc_meschain_marketplace` (`name`, `code`, `settings`, `status`, `sort_order`, `date_added`, `date_modified`) VALUES
('Amazon', 'amazon', '{"api_key":"","api_secret":"","region":"tr"}', 0, 1, NOW(), NOW()),
('Trendyol', 'trendyol', '{"api_key":"","api_secret":""}', 0, 2, NOW(), NOW()),
('N11', 'n11', '{"api_key":"","api_secret":""}', 0, 3, NOW(), NOW()),
('Hepsiburada', 'hepsiburada', '{"api_key":"","api_secret":""}', 0, 4, NOW(), NOW()),
('GittiGidiyor', 'gittigidiyor', '{"api_key":"","api_secret":""}', 0, 5, NOW(), NOW()),
('ÇiçekSepeti', 'ciceksepeti', '{"api_key":"","api_secret":""}', 0, 6, NOW(), NOW()),
('PttAVM', 'pttavm', '{"api_key":"","api_secret":""}', 0, 7, NOW(), NOW());

-- Extension tablosuna MesChain-Sync modülünü ekle
INSERT INTO `oc_extension` (`extension_id`, `type`, `code`, `extension`) VALUES
(NULL, 'module', 'meschain_sync', 'meschain_sync')
ON DUPLICATE KEY UPDATE `type`='module', `code`='meschain_sync', `extension`='meschain_sync';
