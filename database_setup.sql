-- MesChain-Sync Database Setup Script
-- Bu script MesChain-Sync sistemi için gerekli database tablolarını oluşturur
-- OpenCart 3.0.4.0 ile uyumludur

-- API çağrı logları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketplace` varchar(50) NOT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'medium',
  `endpoint` varchar(255) DEFAULT NULL,
  `method` varchar(10) DEFAULT 'GET',
  `call_count` int(11) DEFAULT 1,
  `response_code` int(11) DEFAULT NULL,
  `response_time` decimal(10,3) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_marketplace` (`marketplace`),
  KEY `idx_priority` (`priority`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Senkronizasyon logları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_sync_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` varchar(20) NOT NULL,
  `marketplace` varchar(50) DEFAULT NULL,
  `sync_type` varchar(100) DEFAULT NULL,
  `results` text,
  `success_count` int(11) DEFAULT 0,
  `error_count` int(11) DEFAULT 0,
  `total_processed` int(11) DEFAULT 0,
  `execution_time` decimal(10,3) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_priority` (`priority`),
  KEY `idx_marketplace` (`marketplace`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Webhook logları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_webhook_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketplace` varchar(50) NOT NULL,
  `webhook_type` varchar(100) NOT NULL,
  `payload` text,
  `headers` text,
  `signature` varchar(255) DEFAULT NULL,
  `signature_verified` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'pending',
  `response_code` int(11) DEFAULT NULL,
  `error_message` text,
  `processed_at` datetime DEFAULT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_marketplace` (`marketplace`),
  KEY `idx_webhook_type` (`webhook_type`),
  KEY `idx_status` (`status`),
  KEY `idx_date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Rate limit tracking tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketplace` varchar(50) NOT NULL,
  `limit_type` varchar(20) NOT NULL DEFAULT 'minute',
  `call_count` int(11) DEFAULT 0,
  `limit_value` int(11) NOT NULL,
  `reset_time` int(11) NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_marketplace_type` (`marketplace`, `limit_type`),
  KEY `idx_reset_time` (`reset_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Cron job durumları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_cron_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(100) NOT NULL,
  `priority` varchar(20) NOT NULL,
  `status` varchar(20) DEFAULT 'idle',
  `last_run` datetime DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `run_count` int(11) DEFAULT 0,
  `success_count` int(11) DEFAULT 0,
  `error_count` int(11) DEFAULT 0,
  `last_error` text,
  `execution_time` decimal(10,3) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_job_name` (`job_name`),
  KEY `idx_priority` (`priority`),
  KEY `idx_status` (`status`),
  KEY `idx_last_run` (`last_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Marketplace ayarları tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_marketplace_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketplace` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(20) DEFAULT 'string',
  `is_encrypted` tinyint(1) DEFAULT 0,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_marketplace_key` (`marketplace`, `setting_key`),
  KEY `idx_marketplace` (`marketplace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Queue sistemi tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marketplace` varchar(50) NOT NULL,
  `queue_type` varchar(50) NOT NULL,
  `priority` varchar(20) DEFAULT 'medium',
  `payload` text NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `next_attempt` datetime DEFAULT NULL,
  `error_message` text,
  `processed_at` datetime DEFAULT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_marketplace` (`marketplace`),
  KEY `idx_queue_type` (`queue_type`),
  KEY `idx_priority` (`priority`),
  KEY `idx_status` (`status`),
  KEY `idx_next_attempt` (`next_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Ürün senkronizasyon mapping tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_product_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `marketplace` varchar(50) NOT NULL,
  `marketplace_product_id` varchar(255) NOT NULL,
  `marketplace_sku` varchar(255) DEFAULT NULL,
  `sync_status` varchar(20) DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `sync_errors` text,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_marketplace` (`product_id`, `marketplace`),
  KEY `idx_marketplace_product_id` (`marketplace_product_id`),
  KEY `idx_marketplace_sku` (`marketplace_sku`),
  KEY `idx_sync_status` (`sync_status`),
  FOREIGN KEY (`product_id`) REFERENCES `oc_product` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Sipariş senkronizasyon mapping tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_order_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `marketplace` varchar(50) NOT NULL,
  `marketplace_order_id` varchar(255) NOT NULL,
  `marketplace_status` varchar(100) DEFAULT NULL,
  `sync_status` varchar(20) DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `sync_errors` text,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order_marketplace` (`order_id`, `marketplace`),
  KEY `idx_marketplace_order_id` (`marketplace_order_id`),
  KEY `idx_sync_status` (`sync_status`),
  FOREIGN KEY (`order_id`) REFERENCES `oc_order` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Başlangıç rate limit değerlerini ekle
INSERT IGNORE INTO `oc_meschain_rate_limits` (`marketplace`, `limit_type`, `call_count`, `limit_value`, `reset_time`, `last_updated`) VALUES
('trendyol', 'minute', 0, 30, UNIX_TIMESTAMP() + 60, NOW()),
('trendyol', 'hour', 0, 1000, UNIX_TIMESTAMP() + 3600, NOW()),
('trendyol', 'day', 0, 10000, UNIX_TIMESTAMP() + 86400, NOW()),
('amazon', 'minute', 0, 20, UNIX_TIMESTAMP() + 60, NOW()),
('amazon', 'hour', 0, 800, UNIX_TIMESTAMP() + 3600, NOW()),
('amazon', 'day', 0, 8000, UNIX_TIMESTAMP() + 86400, NOW()),
('n11', 'minute', 0, 40, UNIX_TIMESTAMP() + 60, NOW()),
('n11', 'hour', 0, 1200, UNIX_TIMESTAMP() + 3600, NOW()),
('n11', 'day', 0, 12000, UNIX_TIMESTAMP() + 86400, NOW()),
('hepsiburada', 'minute', 0, 25, UNIX_TIMESTAMP() + 60, NOW()),
('hepsiburada', 'hour', 0, 900, UNIX_TIMESTAMP() + 3600, NOW()),
('hepsiburada', 'day', 0, 15000, UNIX_TIMESTAMP() + 86400, NOW()),
('ebay', 'minute', 0, 35, UNIX_TIMESTAMP() + 60, NOW()),
('ebay', 'day', 0, 5000, UNIX_TIMESTAMP() + 86400, NOW()),
('ebay', 'month', 0, 100000, UNIX_TIMESTAMP() + 2592000, NOW()),
('ozon', 'minute', 0, 30, UNIX_TIMESTAMP() + 60, NOW()),
('ozon', 'hour', 0, 1000, UNIX_TIMESTAMP() + 3600, NOW()),
('ozon', 'day', 0, 10000, UNIX_TIMESTAMP() + 86400, NOW());

-- Başlangıç cron job durumlarını ekle
INSERT IGNORE INTO `oc_meschain_cron_status` (`job_name`, `priority`, `status`, `next_run`, `date_added`, `date_modified`) VALUES
('high_priority_sync', 'high', 'idle', DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW(), NOW()),
('medium_priority_sync', 'medium', 'idle', DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW(), NOW()),
('low_priority_sync', 'low', 'idle', DATE_ADD(NOW(), INTERVAL 60 MINUTE), NOW(), NOW());

-- Başlangıç marketplace ayarlarını ekle
INSERT IGNORE INTO `oc_meschain_marketplace_settings` (`marketplace`, `setting_key`, `setting_value`, `setting_type`, `date_added`, `date_modified`) VALUES
('trendyol', 'api_url', 'https://api.trendyol.com', 'string', NOW(), NOW()),
('trendyol', 'webhook_secret', '', 'string', NOW(), NOW()),
('amazon', 'api_url', 'https://sellingpartnerapi-na.amazon.com', 'string', NOW(), NOW()),
('amazon', 'webhook_secret', '', 'string', NOW(), NOW()),
('n11', 'api_url', 'https://api.n11.com', 'string', NOW(), NOW()),
('n11', 'webhook_secret', '', 'string', NOW(), NOW()),
('hepsiburada', 'api_url', 'https://api.hepsiburada.com', 'string', NOW(), NOW()),
('hepsiburada', 'webhook_secret', '', 'string', NOW(), NOW()),
('ebay', 'api_url', 'https://api.ebay.com', 'string', NOW(), NOW()),
('ebay', 'sandbox_url', 'https://api.sandbox.ebay.com', 'string', NOW(), NOW()),
('ozon', 'api_url', 'https://api-seller.ozon.ru', 'string', NOW(), NOW()),
('ozon', 'webhook_secret', '', 'string', NOW(), NOW());

-- İndeksler ve performans optimizasyonu
CREATE INDEX IF NOT EXISTS `idx_api_logs_marketplace_timestamp` ON `oc_meschain_api_logs` (`marketplace`, `timestamp`);
CREATE INDEX IF NOT EXISTS `idx_sync_logs_priority_timestamp` ON `oc_meschain_sync_logs` (`priority`, `timestamp`);
CREATE INDEX IF NOT EXISTS `idx_webhook_logs_marketplace_status` ON `oc_meschain_webhook_logs` (`marketplace`, `status`);
CREATE INDEX IF NOT EXISTS `idx_queue_status_priority` ON `oc_meschain_queue` (`status`, `priority`);

-- Trigger'lar (Otomatik timestamp güncellemeleri)
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS `tr_meschain_sync_logs_update` 
BEFORE UPDATE ON `oc_meschain_sync_logs`
FOR EACH ROW 
BEGIN
    SET NEW.timestamp = UNIX_TIMESTAMP();
END$$

CREATE TRIGGER IF NOT EXISTS `tr_meschain_cron_status_update` 
BEFORE UPDATE ON `oc_meschain_cron_status`
FOR EACH ROW 
BEGIN
    SET NEW.date_modified = NOW();
END$$

CREATE TRIGGER IF NOT EXISTS `tr_meschain_marketplace_settings_update` 
BEFORE UPDATE ON `oc_meschain_marketplace_settings`
FOR EACH ROW 
BEGIN
    SET NEW.date_modified = NOW();
END$$

DELIMITER ;

-- Başarı mesajı
SELECT 'MesChain-Sync database tables created successfully!' as message; 