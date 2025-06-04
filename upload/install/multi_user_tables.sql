-- MesChain-Sync Multi-User System Database Tables

-- Kullanıcı MesChain ayarları
CREATE TABLE IF NOT EXISTS `oc_user_meschain_settings` (
  `user_id` INT(11) NOT NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'user',
  `marketplace_access` TEXT,
  `dropshipping_enabled` TINYINT(1) NOT NULL DEFAULT '0',
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  `created_by` INT(11) NOT NULL,
  `updated_by` INT(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı API ayarları (pazaryeri bazlı)
CREATE TABLE IF NOT EXISTS `oc_user_api_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `marketplace` VARCHAR(32) NOT NULL,
  `api_data` TEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  `last_sync` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_marketplace` (`user_id`, `marketplace`),
  KEY `marketplace` (`marketplace`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dropshipping siparişleri
CREATE TABLE IF NOT EXISTS `oc_dropshipping_orders` (
  `order_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `marketplace` VARCHAR(32) NOT NULL,
  `marketplace_order_id` VARCHAR(64) NOT NULL,
  `opencart_order_id` INT(11) DEFAULT NULL,
  `supplier_id` INT(11) DEFAULT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(128) NOT NULL,
  `customer_phone` VARCHAR(32) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `billing_address` TEXT NOT NULL,
  `products` TEXT NOT NULL,
  `total_amount` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
  `commission_amount` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `profit_amount` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `tracking_number` VARCHAR(64) DEFAULT NULL,
  `shipping_company` VARCHAR(128) DEFAULT NULL,
  `notes` TEXT,
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  `shipped_date` DATETIME DEFAULT NULL,
  `delivered_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `marketplace_order` (`marketplace`, `marketplace_order_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `created_date` (`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dropshipping tedarikçiler
CREATE TABLE IF NOT EXISTS `oc_dropshipping_suppliers` (
  `supplier_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `company_name` VARCHAR(255) NOT NULL,
  `contact_name` VARCHAR(128) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `phone` VARCHAR(32) NOT NULL,
  `address` TEXT NOT NULL,
  `tax_number` VARCHAR(32) DEFAULT NULL,
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
  `payment_terms` VARCHAR(128) DEFAULT NULL,
  `shipping_info` TEXT,
  `api_endpoint` VARCHAR(255) DEFAULT NULL,
  `api_credentials` TEXT,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`supplier_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dropshipping ürünler
CREATE TABLE IF NOT EXISTS `oc_dropshipping_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `supplier_id` INT(11) NOT NULL,
  `opencart_product_id` INT(11) NOT NULL,
  `supplier_product_id` VARCHAR(64) NOT NULL,
  `supplier_sku` VARCHAR(128) NOT NULL,
  `cost_price` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `selling_price` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `margin_percent` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
  `stock_quantity` INT(11) NOT NULL DEFAULT '0',
  `min_order_quantity` INT(11) NOT NULL DEFAULT '1',
  `shipping_time` VARCHAR(64) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `last_stock_update` DATETIME DEFAULT NULL,
  `last_price_update` DATETIME DEFAULT NULL,
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_supplier_product` (`user_id`, `supplier_id`, `supplier_product_id`),
  KEY `opencart_product_id` (`opencart_product_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı aktivite logları
CREATE TABLE IF NOT EXISTS `oc_user_activity_log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `action` VARCHAR(64) NOT NULL,
  `module` VARCHAR(32) NOT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `data` TEXT,
  `created_date` DATETIME NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `module` (`module`),
  KEY `created_date` (`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Multi-store pazaryeri ayarları
CREATE TABLE IF NOT EXISTS `oc_marketplace_store_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL DEFAULT '0',
  `marketplace` VARCHAR(32) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `api_settings` TEXT NOT NULL,
  `sync_settings` TEXT,
  `commission_settings` TEXT,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_marketplace_user` (`store_id`, `marketplace`, `user_id`),
  KEY `marketplace` (`marketplace`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Pazaryeri komisyon kuralları
CREATE TABLE IF NOT EXISTS `oc_marketplace_commission_rules` (
  `rule_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `marketplace` VARCHAR(32) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `min_price` DECIMAL(15,4) DEFAULT NULL,
  `max_price` DECIMAL(15,4) DEFAULT NULL,
  `commission_type` ENUM('fixed','percentage') NOT NULL DEFAULT 'percentage',
  `commission_value` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `priority` INT(11) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `user_marketplace` (`user_id`, `marketplace`),
  KEY `category_id` (`category_id`),
  KEY `priority` (`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Ürün senkronizasyon kuyruğu
CREATE TABLE IF NOT EXISTS `oc_product_sync_queue` (
  `queue_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `marketplace` VARCHAR(32) NOT NULL,
  `action` ENUM('create','update','delete','price_update','stock_update') NOT NULL,
  `priority` INT(11) NOT NULL DEFAULT '0',
  `data` TEXT,
  `status` ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `attempts` INT(11) NOT NULL DEFAULT '0',
  `error_message` TEXT,
  `created_date` DATETIME NOT NULL,
  `started_date` DATETIME DEFAULT NULL,
  `completed_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `user_marketplace` (`user_id`, `marketplace`),
  KEY `status` (`status`),
  KEY `priority` (`priority`),
  KEY `created_date` (`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı bazlı N11 ürün mappingleri
CREATE TABLE IF NOT EXISTS `oc_user_n11_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `n11_product_id` VARCHAR(64) NOT NULL,
  `n11_seller_code` VARCHAR(64) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `last_updated` DATETIME NOT NULL,
  `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `error_message` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `n11_product_id` (`n11_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı bazlı N11 siparişler
CREATE TABLE IF NOT EXISTS `oc_user_n11_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `n11_order_id` VARCHAR(64) NOT NULL,
  `opencart_order_id` INT(11) DEFAULT NULL,
  `order_number` VARCHAR(64) NOT NULL,
  `status` VARCHAR(32) NOT NULL,
  `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `commission` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_phone` VARCHAR(32) NOT NULL,
  `buyer_email` VARCHAR(128) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `billing_address` TEXT NOT NULL,
  `order_data` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_n11_order` (`user_id`, `n11_order_id`),
  KEY `opencart_order_id` (`opencart_order_id`),
  KEY `status` (`status`),
  KEY `date_added` (`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı bazlı Trendyol ürünler 
CREATE TABLE IF NOT EXISTS `oc_user_trendyol_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `trendyol_product_id` BIGINT(20) DEFAULT NULL,
  `barcode` VARCHAR(128) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `approved` TINYINT(1) NOT NULL DEFAULT '0',
  `last_updated` DATETIME NOT NULL,
  `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `error_message` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `trendyol_product_id` (`trendyol_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı panel ayarları
CREATE TABLE IF NOT EXISTS `oc_user_panel_settings` (
  `user_id` INT(11) NOT NULL,
  `theme` VARCHAR(32) NOT NULL DEFAULT 'default',
  `language` VARCHAR(10) NOT NULL DEFAULT 'tr-tr',
  `timezone` VARCHAR(64) NOT NULL DEFAULT 'Europe/Istanbul',
  `dashboard_widgets` TEXT,
  `notification_settings` TEXT,
  `display_settings` TEXT,
  `created_date` DATETIME NOT NULL,
  `updated_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Sistem bildirimleri
CREATE TABLE IF NOT EXISTS `oc_system_notifications` (
  `notification_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
  `target_users` TEXT,
  `target_roles` TEXT,
  `is_read` TINYINT(1) NOT NULL DEFAULT '0',
  `created_by` INT(11) NOT NULL,
  `created_date` DATETIME NOT NULL,
  `expire_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `type` (`type`),
  KEY `created_date` (`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Kullanıcı bildirim okuma durumları
CREATE TABLE IF NOT EXISTS `oc_user_notification_read` (
  `user_id` INT(11) NOT NULL,
  `notification_id` INT(11) NOT NULL,
  `read_date` DATETIME NOT NULL,
  PRIMARY KEY (`user_id`, `notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci; 