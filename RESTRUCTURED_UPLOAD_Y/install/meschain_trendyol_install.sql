-- MesChain Trendyol Integration Database Tables
-- Version: 1.0.0
-- OpenCart 4.x Compatible

-- Trendyol Products Table
CREATE TABLE IF NOT EXISTS `meschain_trendyol_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `trendyol_id` varchar(50) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `brand` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `list_price` decimal(15,4) DEFAULT '0.0000',
  `sale_price` decimal(15,4) DEFAULT '0.0000',
  `vat_rate` int(11) DEFAULT '18',
  `cargo_company_id` int(11) DEFAULT NULL,
  `shipment_template` varchar(100) DEFAULT NULL,
  `delivery_duration` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT '0',
  `stock_code` varchar(100) DEFAULT NULL,
  `dimension_width` decimal(8,2) DEFAULT '0.00',
  `dimension_height` decimal(8,2) DEFAULT '0.00',
  `dimension_length` decimal(8,2) DEFAULT '0.00',
  `weight` decimal(8,2) DEFAULT '0.00',
  `images` text,
  `attributes` text,
  `status` enum('active','passive','rejected','pending') DEFAULT 'pending',
  `approval_status` varchar(50) DEFAULT NULL,
  `rejection_reason` text,
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `trendyol_id` (`trendyol_id`),
  KEY `barcode` (`barcode`),
  KEY `status` (`status`),
  KEY `last_sync` (`last_sync`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trendyol Orders Table
CREATE TABLE IF NOT EXISTS `meschain_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `trendyol_order_id` varchar(50) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_first_name` varchar(100) NOT NULL,
  `customer_last_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_tax_number` varchar(20) DEFAULT NULL,
  `customer_identity_number` varchar(20) DEFAULT NULL,
  `billing_address` text,
  `shipping_address` text,
  `invoice_address` text,
  `order_date` datetime NOT NULL,
  `ship_by_date` datetime DEFAULT NULL,
  `estimated_delivery_date` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Created',
  `cargo_tracking_number` varchar(100) DEFAULT NULL,
  `cargo_tracking_link` varchar(255) DEFAULT NULL,
  `cargo_sender_number` varchar(100) DEFAULT NULL,
  `cargo_provider_name` varchar(100) DEFAULT NULL,
  `lines` text NOT NULL,
  `total_discount` decimal(15,4) DEFAULT '0.0000',
  `total_tyDiscount` decimal(15,4) DEFAULT '0.0000',
  `total_price` decimal(15,4) NOT NULL,
  `delivery_type` varchar(50) DEFAULT NULL,
  `time_slot_id` int(11) DEFAULT NULL,
  `scheduled_delivery_type` varchar(50) DEFAULT NULL,
  `delivery_address_type` varchar(50) DEFAULT NULL,
  `agreedDeliveryDate` datetime DEFAULT NULL,
  `fastDelivery` tinyint(1) DEFAULT '0',
  `originShipmentDate` datetime DEFAULT NULL,
  `lastModifiedDate` datetime DEFAULT NULL,
  `commercial_invoice_number` varchar(100) DEFAULT NULL,
  `delivery_option` varchar(50) DEFAULT NULL,
  `delivery_method` varchar(50) DEFAULT NULL,
  `sameDayDelivery` tinyint(1) DEFAULT '0',
  `nextDayDelivery` tinyint(1) DEFAULT '0',
  `saturday_delivery` tinyint(1) DEFAULT '0',
  `sunday_delivery` tinyint(1) DEFAULT '0',
  `notes` text,
  `processed` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trendyol_order_id` (`trendyol_order_id`),
  KEY `order_id` (`order_id`),
  KEY `order_number` (`order_number`),
  KEY `status` (`status`),
  KEY `order_date` (`order_date`),
  KEY `processed` (`processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trendyol API Logs Table
CREATE TABLE IF NOT EXISTS `meschain_trendyol_api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(10) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_data` longtext,
  `response_data` longtext,
  `response_code` int(11) DEFAULT NULL,
  `execution_time` decimal(8,4) DEFAULT NULL,
  `error_message` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `method` (`method`),
  KEY `endpoint` (`endpoint`),
  KEY `response_code` (`response_code`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trendyol Webhooks Table
CREATE TABLE IF NOT EXISTS `meschain_trendyol_webhooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `event_time` datetime NOT NULL,
  `trendyol_order_id` varchar(50) DEFAULT NULL,
  `payload` longtext NOT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `processed` tinyint(1) DEFAULT '0',
  `processing_attempts` int(11) DEFAULT '0',
  `last_processing_attempt` datetime DEFAULT NULL,
  `processing_error` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `trendyol_order_id` (`trendyol_order_id`),
  KEY `processed` (`processed`),
  KEY `event_time` (`event_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trendyol Categories Table
CREATE TABLE IF NOT EXISTS `meschain_trendyol_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `path` text,
  `level` int(11) DEFAULT '0',
  `leaf` tinyint(1) DEFAULT '0',
  `attributes` text,
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id` (`category_id`),
  KEY `parent_id` (`parent_id`),
  KEY `level` (`level`),
  KEY `leaf` (`leaf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trendyol Brands Table
CREATE TABLE IF NOT EXISTS `meschain_trendyol_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_id` (`brand_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trendyol Shipment Providers Table
CREATE TABLE IF NOT EXISTS `meschain_trendyol_shipment_providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `tax_number` varchar(20) DEFAULT NULL,
  `last_sync` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_id` (`provider_id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- E-Invoice Table
CREATE TABLE IF NOT EXISTS `meschain_einvoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `invoice_uuid` varchar(50) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `invoice_time` time NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_tax_number` varchar(20) DEFAULT NULL,
  `customer_tax_office` varchar(100) DEFAULT NULL,
  `customer_address` text,
  `invoice_type` varchar(20) DEFAULT 'SATIS',
  `currency` varchar(3) DEFAULT 'TRY',
  `exchange_rate` decimal(10,4) DEFAULT '1.0000',
  `subtotal` decimal(15,4) NOT NULL,
  `tax_amount` decimal(15,4) NOT NULL,
  `total_amount` decimal(15,4) NOT NULL,
  `discount_amount` decimal(15,4) DEFAULT '0.0000',
  `items` longtext NOT NULL,
  `status` enum('draft','created','signed','cancelled') DEFAULT 'draft',
  `portal_status` varchar(50) DEFAULT NULL,
  `ettn` varchar(50) DEFAULT NULL,
  `pdf_url` varchar(500) DEFAULT NULL,
  `html_content` longtext,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_uuid` (`invoice_uuid`),
  KEY `order_id` (`order_id`),
  KEY `invoice_number` (`invoice_number`),
  KEY `status` (`status`),
  KEY `invoice_date` (`invoice_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Barcode Generation Log Table
CREATE TABLE IF NOT EXISTS `meschain_barcodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `barcode_type` varchar(20) NOT NULL DEFAULT 'EAN13',
  `generated_by` int(11) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `product_id` (`product_id`),
  KEY `barcode_type` (`barcode_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table for Extension Configuration
CREATE TABLE IF NOT EXISTS `meschain_trendyol_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` longtext,
  `serialized` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Settings
INSERT INTO `meschain_trendyol_settings` (`key`, `value`, `serialized`) VALUES
('api_url', 'https://api.trendyol.com', 0),
('api_url_sandbox', 'https://stageapi.trendyol.com', 0),
('webhook_events', '["ORDER_CREATED","ORDER_ITEM_REFUND","ORDER_ITEM_CANCELLED","PRODUCT_QUESTIONS_AND_ANSWERS","PRODUCT_REVIEW_AUTOMATION","RETURN_LABEL_GENERATION_REQUEST","SETTLEMENT_REPORT","PRODUCT_STOCK_UPDATED","PRODUCT_PRICE_UPDATED"]', 1),
('default_vat_rate', '18', 0),
('default_cargo_company', '10', 0),
('auto_create_orders', '1', 0),
('auto_update_stock', '1', 0),
('debug_mode', '0', 0),
('rate_limit_per_minute', '600', 0),
('connection_timeout', '30', 0),
('max_retry_attempts', '3', 0),
('retry_delay', '1', 0),
('image_quality', '90', 0),
('max_image_size', '2048', 0),
('barcode_width', '200', 0),
('barcode_height', '80', 0),
('barcode_show_text', '1', 0),
('einvoice_test_mode', '1', 0),
('einvoice_auto_create', '0', 0);

-- Create Indexes for Performance
CREATE INDEX idx_trendyol_products_sync ON meschain_trendyol_products(last_sync, status);
CREATE INDEX idx_trendyol_orders_date_status ON meschain_trendyol_orders(order_date, status);
CREATE INDEX idx_trendyol_api_logs_date ON meschain_trendyol_api_logs(created_at);
CREATE INDEX idx_trendyol_webhooks_processing ON meschain_trendyol_webhooks(processed, processing_attempts);

-- Create Foreign Key Constraints (if tables exist)
-- ALTER TABLE meschain_trendyol_products ADD CONSTRAINT fk_trendyol_product FOREIGN KEY (product_id) REFERENCES oc_product(product_id) ON DELETE CASCADE;
-- ALTER TABLE meschain_trendyol_orders ADD CONSTRAINT fk_trendyol_order FOREIGN KEY (order_id) REFERENCES oc_order(order_id) ON DELETE SET NULL;
-- ALTER TABLE meschain_einvoices ADD CONSTRAINT fk_einvoice_order FOREIGN KEY (order_id) REFERENCES oc_order(order_id) ON DELETE CASCADE;
-- ALTER TABLE meschain_barcodes ADD CONSTRAINT fk_barcode_product FOREIGN KEY (product_id) REFERENCES oc_product(product_id) ON DELETE CASCADE;

-- Create Views for Reporting
CREATE OR REPLACE VIEW meschain_trendyol_stats AS
SELECT
    (SELECT COUNT(*) FROM meschain_trendyol_products WHERE status = 'active') as active_products,
    (SELECT COUNT(*) FROM meschain_trendyol_products WHERE status = 'pending') as pending_products,
    (SELECT COUNT(*) FROM meschain_trendyol_products WHERE status = 'rejected') as rejected_products,
    (SELECT COUNT(*) FROM meschain_trendyol_orders WHERE DATE(order_date) = CURDATE()) as orders_today,
    (SELECT COUNT(*) FROM meschain_trendyol_orders WHERE YEARWEEK(order_date) = YEARWEEK(NOW())) as orders_this_week,
    (SELECT COUNT(*) FROM meschain_trendyol_orders WHERE YEAR(order_date) = YEAR(NOW()) AND MONTH(order_date) = MONTH(NOW())) as orders_this_month,
    (SELECT COUNT(*) FROM meschain_trendyol_orders WHERE status = 'Created') as orders_pending,
    (SELECT COUNT(*) FROM meschain_trendyol_orders WHERE status = 'Shipped') as orders_shipped,
    (SELECT COUNT(*) FROM meschain_trendyol_orders WHERE status = 'Delivered') as orders_delivered,
    (SELECT COUNT(*) FROM meschain_trendyol_webhooks WHERE processed = 0) as pending_webhooks,
    (SELECT MAX(created_at) FROM meschain_trendyol_api_logs) as last_api_call,
    (SELECT MAX(event_time) FROM meschain_trendyol_webhooks) as last_webhook;

-- Create Triggers for Audit Trail
DELIMITER $$

CREATE TRIGGER meschain_trendyol_products_audit
AFTER UPDATE ON meschain_trendyol_products
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO meschain_trendyol_api_logs (method, endpoint, request_data, response_data, created_at)
        VALUES ('AUDIT', 'product_status_change',
                CONCAT('Product ID: ', NEW.product_id, ', Old Status: ', OLD.status),
                CONCAT('New Status: ', NEW.status), NOW());
    END IF;
END$$

CREATE TRIGGER meschain_trendyol_orders_audit
AFTER UPDATE ON meschain_trendyol_orders
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO meschain_trendyol_api_logs (method, endpoint, request_data, response_data, created_at)
        VALUES ('AUDIT', 'order_status_change',
                CONCAT('Order ID: ', NEW.trendyol_order_id, ', Old Status: ', OLD.status),
                CONCAT('New Status: ', NEW.status), NOW());
    END IF;
END$$

DELIMITER ;

-- Create Stored Procedures for Common Operations
DELIMITER $$

CREATE PROCEDURE GetTrendyolDashboardStats()
BEGIN
    SELECT * FROM meschain_trendyol_stats;
END$$

CREATE PROCEDURE CleanupOldLogs(IN days_to_keep INT)
BEGIN
    DELETE FROM meschain_trendyol_api_logs
    WHERE created_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);

    DELETE FROM meschain_trendyol_webhooks
    WHERE processed = 1 AND created_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
END$$

CREATE PROCEDURE GetProductSyncStatus(IN product_id INT)
BEGIN
    SELECT p.*, tp.status, tp.last_sync, tp.rejection_reason
    FROM oc_product p
    LEFT JOIN meschain_trendyol_products tp ON p.product_id = tp.product_id
    WHERE p.product_id = product_id;
END$$

DELIMITER ;

-- Grant necessary permissions (adjust as needed)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON meschain_trendyol_* TO 'opencart_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON meschain_einvoices TO 'opencart_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON meschain_barcodes TO 'opencart_user'@'localhost';

-- Final optimization
OPTIMIZE TABLE meschain_trendyol_products;
OPTIMIZE TABLE meschain_trendyol_orders;
OPTIMIZE TABLE meschain_trendyol_api_logs;
OPTIMIZE TABLE meschain_trendyol_webhooks;

-- --------------------------------------------------------
-- Additional Tables for Cron Job System (Day 5-6)
-- --------------------------------------------------------

-- Enhanced Webhook Logs Table for Cron Processing
CREATE TABLE IF NOT EXISTS `oc_trendyol_webhook_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `event_data` text NOT NULL,
  `received_at` datetime NOT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `processed_at` datetime DEFAULT NULL,
  `processing` tinyint(1) NOT NULL DEFAULT '0',
  `processing_started_at` datetime DEFAULT NULL,
  `retry_count` int(11) NOT NULL DEFAULT '0',
  `next_retry_at` datetime DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`log_id`),
  KEY `idx_processed` (`processed`),
  KEY `idx_processing` (`processing`),
  KEY `idx_received_at` (`received_at`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_retry` (`retry_count`, `next_retry_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sync Logs Table for Cron Job Monitoring
CREATE TABLE IF NOT EXISTS `oc_trendyol_sync_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_type` varchar(50) NOT NULL,
  `status` enum('success','error','warning') NOT NULL,
  `message` text,
  `execution_time` decimal(10,2) DEFAULT NULL,
  `memory_usage` varchar(20) DEFAULT NULL,
  `items_processed` int(11) DEFAULT '0',
  `items_success` int(11) DEFAULT '0',
  `items_failed` int(11) DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_sync_type` (`sync_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Statistics Table
CREATE TABLE IF NOT EXISTS `oc_trendyol_webhook_stats` (
  `stat_id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_received` int(11) NOT NULL DEFAULT '0',
  `total_processed` int(11) NOT NULL DEFAULT '0',
  `total_failed` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock History Table for Tracking Changes
CREATE TABLE IF NOT EXISTS `oc_trendyol_stock_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `old_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `change_type` enum('sync_from_trendyol','sync_to_trendyol','manual','webhook') NOT NULL,
  `change_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_barcode` (`barcode`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sync Queue Table for Background Processing
CREATE TABLE IF NOT EXISTS `oc_trendyol_sync_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_type` enum('product_upload','product_update','stock_update','price_update','order_sync') NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_data` text,
  `priority` tinyint(4) NOT NULL DEFAULT '5',
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `retry_count` int(11) NOT NULL DEFAULT '0',
  `max_retries` int(11) NOT NULL DEFAULT '3',
  `error_message` text,
  `created_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `idx_queue_type` (`queue_type`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alerts Table for System Notifications
CREATE TABLE IF NOT EXISTS `oc_trendyol_alerts` (
  `alert_id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_type` enum('low_stock','sync_error','api_error','webhook_error','system_error') NOT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
  `email_sent` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`alert_id`),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_is_resolved` (`is_resolved`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced Orders Table with OpenCart Integration
CREATE TABLE IF NOT EXISTS `oc_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opencart_order_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `gross_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_discount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `order_date` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Created',
  `tracking_number` varchar(100) DEFAULT NULL,
  `cargo_provider` varchar(100) DEFAULT NULL,
  `order_data` longtext,
  `sync_status` enum('pending','synced','failed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_number` (`order_number`),
  KEY `idx_opencart_order_id` (`opencart_order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_sync_status` (`sync_status`),
  KEY `idx_order_date` (`order_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced Products Table with Sync Status
CREATE TABLE IF NOT EXISTS `oc_trendyol_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opencart_product_id` int(11) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `list_price` decimal(15,4) DEFAULT '0.0000',
  `sale_price` decimal(15,4) DEFAULT '0.0000',
  `quantity` int(11) DEFAULT '0',
  `status` enum('active','passive','rejected','pending') DEFAULT 'pending',
  `approval_status` tinyint(1) DEFAULT NULL,
  `rejection_reason` text,
  `sync_status` enum('pending','synced','failed') NOT NULL DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `last_stock_sync` datetime DEFAULT NULL,
  `last_price_sync` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_barcode` (`barcode`),
  KEY `idx_opencart_product_id` (`opencart_product_id`),
  KEY `idx_status` (`status`),
  KEY `idx_sync_status` (`sync_status`),
  KEY `idx_last_sync` (`last_sync`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create additional indexes for performance
CREATE INDEX idx_trendyol_sync_logs_type_date ON oc_trendyol_sync_logs(sync_type, created_at);
CREATE INDEX idx_trendyol_webhook_logs_event_processed ON oc_trendyol_webhook_logs(event_type, processed);
CREATE INDEX idx_trendyol_stock_history_product_date ON oc_trendyol_stock_history(product_id, created_at);
CREATE INDEX idx_trendyol_sync_queue_status_priority ON oc_trendyol_sync_queue(status, priority);
CREATE INDEX idx_trendyol_alerts_type_severity ON oc_trendyol_alerts(alert_type, severity);

-- Insert default cron settings
INSERT IGNORE INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_status', '0', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_product_sync_enabled', '1', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_order_sync_enabled', '1', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_stock_sync_enabled', '1', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_webhook_processing_enabled', '1', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_product_sync_interval', '60', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_order_sync_interval', '15', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_stock_sync_interval', '30', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_webhook_processing_interval', '5', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_alert_email', '', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_alert_on_error', '1', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_alert_on_low_stock', '1', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_batch_size', '50', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_max_execution_time', '300', 0),
(0, 'meschain_trendyol_cron', 'meschain_trendyol_cron_memory_limit', '256M', 0);

-- Optimize new tables
OPTIMIZE TABLE oc_trendyol_webhook_logs;
OPTIMIZE TABLE oc_trendyol_sync_logs;
OPTIMIZE TABLE oc_trendyol_webhook_stats;
OPTIMIZE TABLE oc_trendyol_stock_history;
OPTIMIZE TABLE oc_trendyol_sync_queue;
OPTIMIZE TABLE oc_trendyol_alerts;
OPTIMIZE TABLE oc_trendyol_orders;
OPTIMIZE TABLE oc_trendyol_products;
