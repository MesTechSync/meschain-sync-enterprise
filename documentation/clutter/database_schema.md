# 🗄️ MesChain-Sync Database Schema Reference

This document outlines the database tables used by the MesChain-Sync extension. Both developers should follow these schemas for consistency and avoid conflicts.

## Core Tables

### `meschain_sync_settings`

Central settings table for the entire extension.

```sql
CREATE TABLE IF NOT EXISTS `meschain_sync_settings` (
  `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL DEFAULT '0',
  `code` VARCHAR(64) NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `value` TEXT NOT NULL,
  `serialized` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `meschain_sync_log`

Central logging table for all marketplace operations.

```sql
CREATE TABLE IF NOT EXISTS `meschain_sync_log` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL DEFAULT '0',
  `marketplace` VARCHAR(32) NOT NULL,
  `action` VARCHAR(64) NOT NULL,
  `status` VARCHAR(32) NOT NULL,
  `message` TEXT NOT NULL,
  `data` TEXT,
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `marketplace` (`marketplace`),
  KEY `action` (`action`),
  KEY `status` (`status`),
  KEY `date_added` (`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

## N11 Integration Tables (Developer 1)

### `n11_category_mapping`

Maps OpenCart categories to N11 marketplace categories.

```sql
CREATE TABLE IF NOT EXISTS `n11_category_mapping` (
  `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
  `opencart_category_id` INT(11) NOT NULL,
  `n11_category_id` VARCHAR(64) NOT NULL,
  `n11_category_name` VARCHAR(255) NOT NULL,
  `n11_category_path` VARCHAR(512) NOT NULL,
  `attributes` TEXT,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`mapping_id`),
  UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `n11_products`

Stores N11-specific product data and synchronization status.

```sql
CREATE TABLE IF NOT EXISTS `n11_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `n11_product_id` VARCHAR(64) NOT NULL,
  `n11_seller_code` VARCHAR(64) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `last_updated` DATETIME NOT NULL,
  `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `error_message` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `n11_product_id` (`n11_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `n11_orders`

Stores orders received from N11.

```sql
CREATE TABLE IF NOT EXISTS `n11_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `n11_order_id` VARCHAR(64) NOT NULL,
  `opencart_order_id` INT(11) DEFAULT NULL,
  `order_number` VARCHAR(64) NOT NULL,
  `status` VARCHAR(32) NOT NULL,
  `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `shipping_cost` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `commission` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_phone` VARCHAR(32) NOT NULL,
  `buyer_email` VARCHAR(128) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `billing_address` TEXT NOT NULL,
  `city` VARCHAR(128) NOT NULL,
  `district` VARCHAR(128) NOT NULL,
  `order_data` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `n11_order_id` (`n11_order_id`),
  KEY `opencart_order_id` (`opencart_order_id`),
  KEY `status` (`status`),
  KEY `date_added` (`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

## Trendyol Integration Tables (Developer 1)

### `trendyol_category_mapping`

Maps OpenCart categories to Trendyol marketplace categories.

```sql
CREATE TABLE IF NOT EXISTS `trendyol_category_mapping` (
  `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
  `opencart_category_id` INT(11) NOT NULL,
  `trendyol_category_id` INT(11) NOT NULL,
  `trendyol_category_name` VARCHAR(255) NOT NULL,
  `trendyol_category_path` VARCHAR(512) NOT NULL,
  `attributes` TEXT,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`mapping_id`),
  UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `trendyol_products`

Stores Trendyol-specific product data and synchronization status.

```sql
CREATE TABLE IF NOT EXISTS `trendyol_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `trendyol_product_id` BIGINT(20) DEFAULT NULL,
  `barcode` VARCHAR(128) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `approved` TINYINT(1) NOT NULL DEFAULT '0',
  `last_updated` DATETIME NOT NULL,
  `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `error_message` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `trendyol_product_id` (`trendyol_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

## Amazon Integration Tables (Developer 2)

### `amazon_category_mapping`

Maps OpenCart categories to Amazon marketplace categories.

```sql
CREATE TABLE IF NOT EXISTS `amazon_category_mapping` (
  `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
  `opencart_category_id` INT(11) NOT NULL,
  `amazon_category_id` VARCHAR(128) NOT NULL,
  `amazon_category_name` VARCHAR(255) NOT NULL,
  `amazon_browse_node_id` VARCHAR(32) NOT NULL,
  `item_type` VARCHAR(128) NOT NULL,
  `attributes` TEXT,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`mapping_id`),
  UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `amazon_products`

Stores Amazon-specific product data and synchronization status.

```sql
CREATE TABLE IF NOT EXISTS `amazon_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `asin` VARCHAR(32) DEFAULT NULL,
  `seller_sku` VARCHAR(64) NOT NULL,
  `amazon_status` VARCHAR(32) DEFAULT NULL,
  `fulfillment_channel` VARCHAR(32) NOT NULL DEFAULT 'MERCHANT',
  `condition` VARCHAR(32) NOT NULL DEFAULT 'New',
  `price_rule_id` INT(11) DEFAULT NULL,
  `last_updated` DATETIME NOT NULL,
  `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `error_message` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `asin` (`asin`),
  KEY `seller_sku` (`seller_sku`),
  KEY `sync_status` (`sync_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `amazon_orders`

Stores orders received from Amazon.

```sql
CREATE TABLE IF NOT EXISTS `amazon_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `amazon_order_id` VARCHAR(32) NOT NULL,
  `opencart_order_id` INT(11) DEFAULT NULL,
  `purchase_date` DATETIME NOT NULL,
  `last_update_date` DATETIME NOT NULL,
  `order_status` VARCHAR(32) NOT NULL,
  `fulfillment_channel` VARCHAR(32) NOT NULL,
  `sales_channel` VARCHAR(64) NOT NULL,
  `ship_service_level` VARCHAR(64) NOT NULL,
  `order_total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  `order_currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
  `number_of_items_shipped` INT(11) NOT NULL DEFAULT '0',
  `number_of_items_unshipped` INT(11) NOT NULL DEFAULT '0',
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_email` VARCHAR(128) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_city` VARCHAR(128) NOT NULL,
  `shipping_state` VARCHAR(128) NOT NULL,
  `shipping_postal_code` VARCHAR(32) NOT NULL,
  `shipping_country` VARCHAR(2) NOT NULL,
  `shipping_phone` VARCHAR(32) NOT NULL,
  `order_data` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `amazon_order_id` (`amazon_order_id`),
  KEY `opencart_order_id` (`opencart_order_id`),
  KEY `order_status` (`order_status`),
  KEY `purchase_date` (`purchase_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

## Hepsiburada Integration Tables (Developer 2)

### `hepsiburada_category_mapping`

Maps OpenCart categories to Hepsiburada marketplace categories.

```sql
CREATE TABLE IF NOT EXISTS `hepsiburada_category_mapping` (
  `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
  `opencart_category_id` INT(11) NOT NULL,
  `hepsiburada_category_id` VARCHAR(64) NOT NULL,
  `hepsiburada_category_name` VARCHAR(255) NOT NULL,
  `attributes` TEXT,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`mapping_id`),
  UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `hepsiburada_products`

Stores Hepsiburada-specific product data and synchronization status.

```sql
CREATE TABLE IF NOT EXISTS `hepsiburada_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `merchant_id` VARCHAR(64) NOT NULL,
  `hepsiburada_product_id` VARCHAR(64) DEFAULT NULL,
  `barcode` VARCHAR(128) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  `last_updated` DATETIME NOT NULL,
  `sync_status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `error_message` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `hepsiburada_product_id` (`hepsiburada_product_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

## UI Components Tables (Shared)

### `meschain_sync_user_settings`

Stores user-specific settings like themes and preferences.

```sql
CREATE TABLE IF NOT EXISTS `meschain_sync_user_settings` (
  `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `user_id_key` (`user_id`,`setting_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

### `meschain_sync_announcements`

Stores system announcements for users.

```sql
CREATE TABLE IF NOT EXISTS `meschain_sync_announcements` (
  `announcement_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `roles` TEXT NOT NULL,
  `date` DATETIME NOT NULL,
  `expire` DATETIME DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `template` VARCHAR(32) NOT NULL DEFAULT 'klasik',
  `seen_by` TEXT,
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`announcement_id`),
  KEY `active` (`active`),
  KEY `date` (`date`),
  KEY `expire` (`expire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
```

## Important Notes

1. **Table Naming Convention**: 
   - Core system tables: `meschain_sync_*`
   - Marketplace-specific tables: `{marketplace_name}_*`

2. **Character Set & Collation**:
   - All tables should use `utf8` character set and `utf8_general_ci` collation

3. **Foreign Keys**:
   - MyISAM doesn't support foreign keys, but maintain referential integrity in the application code
   - OpenCart primarily uses MyISAM storage engine

4. **Installation & Upgrades**:
   - Each module should have install/uninstall methods in its controller
   - Use database schema versioning to handle upgrades

5. **Modification Guidelines**:
   - Always create new columns rather than modifying existing ones
   - Use ALTER TABLE ADD if possible, not DROP or CHANGE
   - Coordinate schema changes between developers

6. **Data Migration**:
   - Always back up data before schema changes
   - Include data migration scripts when table structures change

---

This schema documentation will be updated as the project evolves. Both developers should follow these structures and coordinate any necessary changes. 