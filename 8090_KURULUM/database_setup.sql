-- ============================================================================
-- MesChain Trendyol 8090 Port Özel Veritabanı Kurulum Scripti
-- OpenCart 8090 Port + MesChain Trendyol Entegrasyonu için özel veritabanı yapısı
-- ============================================================================

-- Veritabanı oluştur
CREATE DATABASE IF NOT EXISTS `meschain_test_8090` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Kullanıcı oluştur
CREATE USER IF NOT EXISTS 'opencart_8090'@'localhost' IDENTIFIED BY 'oc8090_secure_password_2025';

-- Yetkileri ver
GRANT ALL PRIVILEGES ON `meschain_test_8090`.* TO 'opencart_8090'@'localhost';
FLUSH PRIVILEGES;

-- Veritabanını kullan
USE `meschain_test_8090`;

-- ============================================================================
-- MESCHAIN TRENDYOL ÖZEL TABLOLAR
-- ============================================================================

-- MesChain Trendyol Ürünler Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `trendyol_product_id` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `trendyol_category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `price` decimal(15,4) DEFAULT 0.0000,
  `sale_price` decimal(15,4) DEFAULT 0.0000,
  `list_price` decimal(15,4) DEFAULT 0.0000,
  `cargo_company_id` int(11) DEFAULT NULL,
  `delivery_duration` int(11) DEFAULT 3,
  `status` tinyint(1) DEFAULT 1,
  `sync_status` enum('pending','syncing','synced','error','disabled') DEFAULT 'pending',
  `approval_status` enum('waiting','approved','rejected') DEFAULT 'waiting',
  `last_sync` datetime DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product` (`product_id`),
  KEY `trendyol_product_id` (`trendyol_product_id`),
  KEY `sync_status` (`sync_status`),
  KEY `approval_status` (`approval_status`),
  KEY `last_sync` (`last_sync`),
  KEY `brand_category` (`brand_id`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='MesChain Trendyol Ürün Senkronizasyon Tablosu';

-- MesChain Trendyol Siparişler Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `trendyol_order_id` varchar(255) NOT NULL,
  `shipment_package_id` varchar(255) DEFAULT NULL,
  `order_number` varchar(100) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `shipment_status` varchar(50) DEFAULT NULL,
  `cargo_tracking_number` varchar(255) DEFAULT NULL,
  `cargo_provider_name` varchar(100) DEFAULT NULL,
  `cargo_tracking_link` varchar(500) DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `total_amount` decimal(15,4) DEFAULT 0.0000,
  `commission_amount` decimal(15,4) DEFAULT 0.0000,
  `sync_status` enum('pending','syncing','synced','error','cancelled') DEFAULT 'pending',
  `webhook_received` tinyint(1) DEFAULT 0,
  `last_sync` datetime DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order` (`order_id`),
  UNIQUE KEY `unique_trendyol_order` (`trendyol_order_id`),
  KEY `sync_status` (`sync_status`),
  KEY `order_status` (`order_status`),
  KEY `shipment_status` (`shipment_status`),
  KEY `last_sync` (`last_sync`),
  KEY `webhook_received` (`webhook_received`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='MesChain Trendyol Sipariş Senkronizasyon Tablosu';

-- MesChain Trendyol Ayarlar Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','integer','boolean','json','encrypted') DEFAULT 'string',
  `category` varchar(100) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `validation_rule` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `category` (`category`),
  KEY `is_required` (`is_required`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='MesChain Trendyol Ayarlar Tablosu';

-- MesChain Trendyol Loglar Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_level` enum('debug','info','warning','error','critical') DEFAULT 'info',
  `operation` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `context_data` json DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `execution_time` decimal(8,4) DEFAULT NULL,
  `memory_usage` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `log_level` (`log_level`),
  KEY `operation` (`operation`),
  KEY `created_at` (`created_at`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='MesChain Trendyol İşlem Logları';

-- MesChain Trendyol Kategori Eşleştirme Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opencart_category_id` int(11) NOT NULL,
  `trendyol_category_id` int(11) NOT NULL,
  `trendyol_category_name` varchar(255) NOT NULL,
  `trendyol_category_path` text DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `vat_rate` decimal(5,2) DEFAULT 18.00,
  `attributes_mapping` json DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `auto_sync` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_mapping` (`opencart_category_id`, `trendyol_category_id`),
  KEY `opencart_category_id` (`opencart_category_id`),
  KEY `trendyol_category_id` (`trendyol_category_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OpenCart-Trendyol Kategori Eşleştirme';

-- MesChain Trendyol Marka Eşleştirme Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opencart_manufacturer_id` int(11) NOT NULL,
  `trendyol_brand_id` int(11) NOT NULL,
  `trendyol_brand_name` varchar(255) NOT NULL,
  `brand_approval_status` enum('waiting','approved','rejected') DEFAULT 'waiting',
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_brand_mapping` (`opencart_manufacturer_id`, `trendyol_brand_id`),
  KEY `opencart_manufacturer_id` (`opencart_manufacturer_id`),
  KEY `trendyol_brand_id` (`trendyol_brand_id`),
  KEY `brand_approval_status` (`brand_approval_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OpenCart-Trendyol Marka Eşleştirme';

-- MesChain Trendyol Webhook Tablosu
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_webhooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webhook_type` varchar(100) NOT NULL,
  `webhook_data` json NOT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `processed` tinyint(1) DEFAULT 0,
  `processing_result` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhook_type` (`webhook_type`),
  KEY `processed` (`processed`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Trendyol Webhook İstekleri';

-- MesChain Trendyol Senkronizasyon Geçmişi
CREATE TABLE IF NOT EXISTS `oc_meschain_trendyol_sync_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_type` enum('products','orders','stock','price','all') NOT NULL,
  `sync_mode` enum('manual','auto','scheduled') DEFAULT 'manual',
  `started_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `status` enum('running','completed','failed','cancelled') DEFAULT 'running',
  `total_items` int(11) DEFAULT 0,
  `processed_items` int(11) DEFAULT 0,
  `success_items` int(11) DEFAULT 0,
  `failed_items` int(11) DEFAULT 0,
  `error_summary` text DEFAULT NULL,
  `execution_time` decimal(8,4) DEFAULT NULL,
  `memory_peak` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sync_type` (`sync_type`),
  KEY `sync_mode` (`sync_mode`),
  KEY `status` (`status`),
  KEY `started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Senkronizasyon Geçmişi';

-- ============================================================================
-- VARSAYILAN AYARLAR
-- ============================================================================

-- Temel API Ayarları
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
('api_key', '', 'encrypted', 'api', 'Trendyol API Anahtarı', 1),
('api_secret', '', 'encrypted', 'api', 'Trendyol API Gizli Anahtarı', 1),
('supplier_id', '', 'string', 'api', 'Trendyol Tedarikçi ID', 1),
('environment', 'sandbox', 'string', 'api', 'API Ortamı (sandbox/production)', 1),
('api_base_url', 'https://api.trendyol.com', 'string', 'api', 'Trendyol API Base URL', 1);

-- Senkronizasyon Ayarları
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
('auto_sync_enabled', '0', 'boolean', 'sync', 'Otomatik Senkronizasyon', 0),
('sync_interval', '3600', 'integer', 'sync', 'Senkronizasyon Aralığı (saniye)', 0),
('batch_size', '50', 'integer', 'sync', 'Toplu İşlem Boyutu', 0),
('max_retry_count', '3', 'integer', 'sync', 'Maksimum Yeniden Deneme', 0),
('connection_timeout', '30', 'integer', 'sync', 'Bağlantı Zaman Aşımı (saniye)', 0),
('request_delay', '1000', 'integer', 'sync', 'İstekler Arası Gecikme (ms)', 0);

-- Ürün Ayarları
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
('default_brand_id', '1', 'integer', 'product', 'Varsayılan Marka ID', 0),
('default_category_id', '1', 'integer', 'product', 'Varsayılan Kategori ID', 0),
('stock_buffer', '5', 'integer', 'product', 'Stok Tamponu', 0),
('price_margin', '0', 'integer', 'product', 'Fiyat Marjı (%)', 0),
('auto_approve_products', '0', 'boolean', 'product', 'Ürünleri Otomatik Onayla', 0),
('min_stock_quantity', '1', 'integer', 'product', 'Minimum Stok Miktarı', 0),
('max_stock_quantity', '9999', 'integer', 'product', 'Maksimum Stok Miktarı', 0);

-- Sipariş Ayarları
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
('auto_create_orders', '1', 'boolean', 'order', 'Siparişleri Otomatik Oluştur', 0),
('default_order_status_id', '1', 'integer', 'order', 'Varsayılan Sipariş Durumu', 0),
('shipped_order_status_id', '3', 'integer', 'order', 'Kargolanan Sipariş Durumu', 0),
('delivered_order_status_id', '5', 'integer', 'order', 'Teslim Edilen Sipariş Durumu', 0),
('cancelled_order_status_id', '7', 'integer', 'order', 'İptal Edilen Sipariş Durumu', 0);

-- Webhook Ayarları
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
('webhook_enabled', '0', 'boolean', 'webhook', 'Webhook Etkin', 0),
('webhook_url', '', 'string', 'webhook', 'Webhook URL', 0),
('webhook_secret', '', 'encrypted', 'webhook', 'Webhook Gizli Anahtarı', 0),
('webhook_events', '["order_created","order_updated","product_updated"]', 'json', 'webhook', 'Webhook Olayları', 0);

-- Sistem Ayarları
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
('debug_mode', '1', 'boolean', 'system', 'Debug Modu', 0),
('log_level', 'info', 'string', 'system', 'Log Seviyesi', 0),
('notification_email', '', 'string', 'system', 'Bildirim E-posta', 0),
('integration_status', 'testing', 'string', 'system', 'Entegrasyon Durumu', 0),
('last_health_check', '', 'string', 'system', 'Son Sağlık Kontrolü', 0);

-- Test Ayarları (8090 Port Özel)
INSERT IGNORE INTO `oc_meschain_trendyol_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_required`) VALUES
('test_mode', '1', 'boolean', 'test', 'Test Modu', 0),
('test_supplier_id', 'TEST_SUPPLIER', 'string', 'test', 'Test Tedarikçi ID', 0),
('test_api_key', 'TEST_API_KEY', 'string', 'test', 'Test API Anahtarı', 0),
('test_product_limit', '10', 'integer', 'test', 'Test Ürün Limiti', 0),
('test_order_limit', '5', 'integer', 'test', 'Test Sipariş Limiti', 0);

-- ============================================================================
-- PERFORMANS İNDEKSLERİ
-- ============================================================================

-- Ürünler tablosu için ek indeksler
CREATE INDEX IF NOT EXISTS `idx_products_sync_status_date` ON `oc_meschain_trendyol_products` (`sync_status`, `last_sync`);
CREATE INDEX IF NOT EXISTS `idx_products_approval_status` ON `oc_meschain_trendyol_products` (`approval_status`, `status`);
CREATE INDEX IF NOT EXISTS `idx_products_retry_count` ON `oc_meschain_trendyol_products` (`retry_count`, `sync_status`);

-- Siparişler tablosu için ek indeksler
CREATE INDEX IF NOT EXISTS `idx_orders_sync_status_date` ON `oc_meschain_trendyol_orders` (`sync_status`, `last_sync`);
CREATE INDEX IF NOT EXISTS `idx_orders_status_combo` ON `oc_meschain_trendyol_orders` (`order_status`, `shipment_status`);
CREATE INDEX IF NOT EXISTS `idx_orders_webhook_status` ON `oc_meschain_trendyol_orders` (`webhook_received`, `sync_status`);

-- Loglar tablosu için ek indeksler
CREATE INDEX IF NOT EXISTS `idx_logs_level_operation` ON `oc_meschain_trendyol_logs` (`log_level`, `operation`);
CREATE INDEX IF NOT EXISTS `idx_logs_date_level` ON `oc_meschain_trendyol_logs` (`created_at`, `log_level`);

-- Webhook tablosu için ek indeksler
CREATE INDEX IF NOT EXISTS `idx_webhooks_processed_type` ON `oc_meschain_trendyol_webhooks` (`processed`, `webhook_type`);
CREATE INDEX IF NOT EXISTS `idx_webhooks_retry_count` ON `oc_meschain_trendyol_webhooks` (`retry_count`, `processed`);

-- ============================================================================
-- VERİ DOĞRULAMA VE KONTROL
-- ============================================================================

-- Tablo sayısını kontrol et
SELECT
    'MesChain Trendyol Tabloları' as 'Kontrol',
    COUNT(*) as 'Tablo Sayısı'
FROM information_schema.tables
WHERE table_schema = 'meschain_test_8090'
AND table_name LIKE '%meschain_trendyol%';

-- Ayar sayısını kontrol et
SELECT
    'Varsayılan Ayarlar' as 'Kontrol',
    COUNT(*) as 'Ayar Sayısı'
FROM oc_meschain_trendyol_settings;

-- Kategori bazında ayar dağılımı
SELECT
    category as 'Kategori',
    COUNT(*) as 'Ayar Sayısı'
FROM oc_meschain_trendyol_settings
GROUP BY category
ORDER BY category;

-- İndeks kontrolü
SELECT
    table_name as 'Tablo',
    index_name as 'İndeks',
    column_name as 'Kolon'
FROM information_schema.statistics
WHERE table_schema = 'meschain_test_8090'
AND table_name LIKE '%meschain_trendyol%'
ORDER BY table_name, index_name;

-- ============================================================================
-- KURULUM TAMAMLANDI
-- ============================================================================

SELECT
    'MesChain Trendyol 8090 Port Veritabanı Kurulumu' as 'Durum',
    'BAŞARILI' as 'Sonuç',
    NOW() as 'Tamamlanma Tarihi';

-- Kurulum özeti
SELECT
    'Kurulum Özeti' as '=== ÖZET ===',
    '' as '';

SELECT 'Veritabanı' as 'Bileşen', 'meschain_test_8090' as 'Değer'
UNION ALL
SELECT 'Kullanıcı', 'opencart_8090'
UNION ALL
SELECT 'Tablo Sayısı', CAST(COUNT(*) as CHAR) FROM information_schema.tables WHERE table_schema = 'meschain_test_8090' AND table_name LIKE '%meschain_trendyol%'
UNION ALL
SELECT 'Ayar Sayısı', CAST(COUNT(*) as CHAR) FROM oc_meschain_trendyol_settings
UNION ALL
SELECT 'Test Modu', 'Etkin'
UNION ALL
SELECT 'Ortam', 'Sandbox/Testing';
