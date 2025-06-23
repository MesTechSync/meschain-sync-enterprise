-- MesChain-Sync Enterprise Performance Optimization Indexes
-- Version: 3.0.0
-- Date: 2025-06-18

-- Product sync performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_product_sync_marketplace
ON oc_meschain_product_sync (marketplace, sync_status, last_sync);

CREATE INDEX IF NOT EXISTS idx_meschain_product_sync_status_date
ON oc_meschain_product_sync (sync_status, date_modified);

CREATE INDEX IF NOT EXISTS idx_meschain_product_sync_next
ON oc_meschain_product_sync (next_sync, auto_sync);

-- Order integration performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_order_integration_marketplace
ON oc_meschain_order_integration (marketplace, integration_status);

CREATE INDEX IF NOT EXISTS idx_meschain_order_integration_date
ON oc_meschain_order_integration (date_integrated, marketplace);

CREATE INDEX IF NOT EXISTS idx_meschain_order_marketplace_order
ON oc_meschain_order_integration (marketplace_order_id);

-- Settings performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_settings_marketplace_key
ON oc_meschain_settings (marketplace, setting_key);

CREATE INDEX IF NOT EXISTS idx_meschain_settings_group
ON oc_meschain_settings (setting_group, status);

-- Logs performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_logs_level_date
ON oc_meschain_logs (log_level, date_added);

CREATE INDEX IF NOT EXISTS idx_meschain_logs_type_action
ON oc_meschain_logs (log_type, log_action);

CREATE INDEX IF NOT EXISTS idx_meschain_logs_marketplace
ON oc_meschain_logs (marketplace, date_added);

-- Analytics performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_analytics_entity
ON oc_meschain_ai_analytics (entity_type, entity_id, analytics_type);

CREATE INDEX IF NOT EXISTS idx_meschain_analytics_date
ON oc_meschain_ai_analytics (date_analyzed, date_expires);

-- Metrics performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_metrics_type_date
ON oc_meschain_metrics (metric_type, date_added);

-- Rate limits performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_rate_limits_key
ON oc_meschain_rate_limits (`key`, window_start);

-- Audit log performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_audit_category_date
ON oc_meschain_audit_log (category, date_added);

CREATE INDEX IF NOT EXISTS idx_meschain_audit_user
ON oc_meschain_audit_log (user_id, date_added);

-- Slow queries performance indexes
CREATE INDEX IF NOT EXISTS idx_meschain_slow_queries_date
ON oc_meschain_slow_queries (date_added, execution_time);

-- Create additional tables for performance optimization
CREATE TABLE IF NOT EXISTS `oc_meschain_rate_limits` (
    `key` varchar(255) NOT NULL,
    `attempts` int(11) NOT NULL DEFAULT '0',
    `window_start` int(11) NOT NULL,
    PRIMARY KEY (`key`),
    KEY `idx_window` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oc_meschain_audit_log` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT '0',
    `username` varchar(96) NOT NULL,
    `category` varchar(50) NOT NULL,
    `action` varchar(100) NOT NULL,
    `data` longtext,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` varchar(500) NOT NULL,
    `date_added` datetime NOT NULL,
    PRIMARY KEY (`log_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_category_date` (`category`, `date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `oc_meschain_slow_queries` (
    `query_id` int(11) NOT NULL AUTO_INCREMENT,
    `query_sql` longtext NOT NULL,
    `execution_time` decimal(10,4) NOT NULL,
    `date_added` datetime NOT NULL,
    PRIMARY KEY (`query_id`),
    KEY `idx_date_time` (`date_added`, `execution_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Optimize existing tables
OPTIMIZE TABLE oc_meschain_product_sync;
OPTIMIZE TABLE oc_meschain_order_integration;
OPTIMIZE TABLE oc_meschain_settings;
OPTIMIZE TABLE oc_meschain_logs;
OPTIMIZE TABLE oc_meschain_ai_analytics;
OPTIMIZE TABLE oc_meschain_metrics;
