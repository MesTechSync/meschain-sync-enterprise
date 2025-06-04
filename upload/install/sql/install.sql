-- MesChain Sync Kurulum SQL Dosyası
-- Versiyon: 1.0.1
-- Tarih: 2023-11-20

-- Duyuru tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_announcement` (
  `announcement_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'info',
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `created_by` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`announcement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Duyuru hedef kullanıcıları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_announcement_user` (
  `announcement_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `read` TINYINT(1) NOT NULL DEFAULT '0',
  `read_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`announcement_id`, `user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı ayarları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_user_setting` (
  `user_id` INT(11) NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`user_id`, `key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Pazaryeri entegrasyon ayarları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_marketplace` (
  `marketplace_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(64) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `settings` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`marketplace_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Ürün eşleştirme tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_product_mapping` (
  `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `marketplace_id` INT(11) NOT NULL,
  `marketplace_product_id` VARCHAR(255) NOT NULL,
  `marketplace_category_id` VARCHAR(255) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`mapping_id`),
  KEY `product_id` (`product_id`),
  KEY `marketplace_id` (`marketplace_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Sipariş eşleştirme tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_order_mapping` (
  `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `marketplace_id` INT(11) NOT NULL,
  `marketplace_order_id` VARCHAR(255) NOT NULL,
  `marketplace_order_status` VARCHAR(64) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`mapping_id`),
  KEY `order_id` (`order_id`),
  KEY `marketplace_id` (`marketplace_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Tema ayarları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_theme` (
  `theme_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `code` VARCHAR(64) NOT NULL,
  `settings` TEXT NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`theme_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Varsayılan tema ekle
INSERT INTO `oc_meschain_theme` (`name`, `code`, `settings`, `is_default`) VALUES
('Sütlü Kahve & Deniz Mavisi', 'sutlu_kahve', '{"primary_color":"#3498db","secondary_color":"#996633","background":"#f5f5f5","text_color":"#333333","link_color":"#2980b9"}', 1);

-- Varsayılan pazaryerleri ekle
INSERT INTO `oc_meschain_marketplace` (`code`, `name`, `status`, `settings`, `date_added`, `date_modified`) VALUES
('trendyol', 'Trendyol', 0, '{}', NOW(), NOW()),
('n11', 'n11', 0, '{}', NOW(), NOW()),
('amazon', 'Amazon', 0, '{}', NOW(), NOW()),
('ebay', 'eBay', 0, '{}', NOW(), NOW()),
('hepsiburada', 'Hepsiburada', 0, '{}', NOW(), NOW()),
('ozon', 'Ozon', 0, '{}', NOW(), NOW());

-- Trendyol webhook log tablosu
CREATE TABLE IF NOT EXISTS `oc_trendyol_webhook_log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(50) NOT NULL,
  `message` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'success',
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` TEXT,
  PRIMARY KEY (`log_id`),
  KEY `event_type` (`event_type`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;