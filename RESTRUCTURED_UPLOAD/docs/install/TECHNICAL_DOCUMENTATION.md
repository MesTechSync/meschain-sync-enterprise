# MESCHAIN-SYNC ENTERPRISE TECHNICAL DOCUMENTATION

**Version:** 3.0.0
**Last Updated:** June 18, 2025
**Platform:** OpenCart 4.0.2.3

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Installation Guide](#installation-guide)
3. [Configuration](#configuration)
4. [API Reference](#api-reference)
5. [Database Schema](#database-schema)
6. [Security Guidelines](#security-guidelines)
7. [Performance Optimization](#performance-optimization)
8. [Troubleshooting](#troubleshooting)

---

## System Architecture

### Overview

MesChain-Sync Enterprise is a comprehensive marketplace integration solution built as a native OpenCart 4.0.2.3 extension. The system follows a modular architecture with complete Azure cloud services integration.

### Component Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    OpenCart 4.0.2.3                         │
├─────────────────────────────────────────────────────────────┤
│                  MesChain-Sync Enterprise                   │
├──────────────────┬──────────────────┬──────────────────────┤
│   Controllers    │     Models       │    Libraries         │
├──────────────────┼──────────────────┼──────────────────────┤
│ • Dashboard      │ • Product Sync   │ • Azure Manager      │
│ • Marketplace    │ • Order Sync     │ • API Clients        │
│ • Analytics      │ • Inventory      │ • Security Manager   │
│ • Settings       │ • Metrics        │ • Performance Engine │
└──────────────────┴──────────────────┴──────────────────────┘
```

### Directory Structure

```
RESTRUCTURED_UPLOAD/
├── admin/
│   ├── controller/extension/module/meschain/
│   ├── model/extension/module/meschain/
│   ├── view/template/extension/module/meschain/
│   └── language/[locale]/extension/module/meschain/
├── system/
│   └── library/meschain/
│       ├── api/          # Marketplace API clients
│       ├── azure/        # Azure service integrations
│       ├── security/     # Security components
│       ├── performance/  # Performance optimization
│       └── monitoring/   # Real-time monitoring
├── sql/                  # Database scripts
├── tests/               # Test suites
└── docs/               # Documentation

```

---

## Installation Guide

### Prerequisites

- OpenCart 4.0.2.3
- PHP 8.0 or higher
- MySQL 8.0 or higher
- SSL certificate (required)
- Minimum 2GB RAM
- 10GB available disk space

### Installation Steps

1. **Backup Your Store**
   ```bash
   # Create full backup
   mysqldump -u [username] -p [database_name] > backup_$(date +%Y%m%d).sql
   cp -r /path/to/opencart /path/to/backup/
   ```

2. **Upload Extension**
   - Navigate to Admin Panel → Extensions → Installer
   - Upload `meschain_sync_enterprise.ocmod.zip`
   - Wait for successful upload confirmation

3. **Install Extension**
   - Go to Extensions → Extensions → Modules
   - Find "MesChain Sync Enterprise"
   - Click Install button
   - Click Edit to configure

4. **Initial Configuration**
   - Enter your marketplace API credentials
   - Configure Azure services (optional)
   - Set up cron jobs for automation
   - Test marketplace connections

### Cron Job Setup

Add the following entries to your crontab:

```bash
# Product synchronization (every 5 minutes)
*/5 * * * * curl -s "https://yourstore.com/index.php?route=extension/module/meschain/cron&task=sync&token=YOUR_TOKEN"

# Order processing (every 3 minutes)
*/3 * * * * curl -s "https://yourstore.com/index.php?route=extension/module/meschain/cron&task=orders&token=YOUR_TOKEN"

# Analytics update (hourly)
0 * * * * curl -s "https://yourstore.com/index.php?route=extension/module/meschain/cron&task=analytics&token=YOUR_TOKEN"

# Cleanup (daily at 2 AM)
0 2 * * * curl -s "https://yourstore.com/index.php?route=extension/module/meschain/cron&task=cleanup&token=YOUR_TOKEN"
```

---

## Configuration

### General Settings

```php
// config/meschain.php
return [
    'general' => [
        'debug_mode' => false,
        'log_level' => 'info', // debug, info, warning, error
        'timezone' => 'Europe/Istanbul',
        'language' => 'tr-tr',
        'currency' => 'TRY'
    ],
    'performance' => [
        'cache_enabled' => true,
        'cache_ttl' => 3600,
        'batch_size' => 100,
        'max_workers' => 4
    ],
    'security' => [
        'api_rate_limit' => 1000, // requests per hour
        'encryption_key' => env('MESCHAIN_ENCRYPTION_KEY'),
        'ssl_verify' => true,
        'ip_whitelist' => []
    ]
];
```

### Marketplace Configuration

Each marketplace requires specific configuration:

#### Trendyol
```php
'trendyol' => [
    'api_url' => 'https://api.trendyol.com/sapigw',
    'supplier_id' => 'YOUR_SUPPLIER_ID',
    'api_key' => 'YOUR_API_KEY',
    'api_secret' => 'YOUR_API_SECRET',
    'test_mode' => false
]
```

#### Amazon SP-API
```php
'amazon' => [
    'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
    'marketplace_id' => 'A1PA6795UKMFR9',
    'seller_id' => 'YOUR_SELLER_ID',
    'access_key' => 'YOUR_ACCESS_KEY',
    'secret_key' => 'YOUR_SECRET_KEY',
    'role_arn' => 'YOUR_ROLE_ARN'
]
```

---

## API Reference

### Authentication

All API endpoints require authentication using Bearer tokens:

```http
Authorization: Bearer YOUR_API_TOKEN
```

### Endpoints

#### Product Management

**Get Products**
```http
GET /api/meschain/products
```

Parameters:
- `marketplace` (string): Filter by marketplace
- `status` (string): sync_status filter
- `limit` (integer): Results per page
- `page` (integer): Page number

Response:
```json
{
    "success": true,
    "data": {
        "products": [...],
        "total": 1250,
        "page": 1,
        "limit": 50
    }
}
```

**Sync Product**
```http
POST /api/meschain/products/{product_id}/sync
```

Body:
```json
{
    "marketplaces": ["trendyol", "amazon", "n11"],
    "force": false
}
```

#### Order Management

**Get Orders**
```http
GET /api/meschain/orders
```

**Process Order**
```http
POST /api/meschain/orders/{order_id}/process
```

#### Analytics

**Get Dashboard Metrics**
```http
GET /api/meschain/analytics/dashboard
```

**Get Sales Report**
```http
GET /api/meschain/analytics/sales
```

Parameters:
- `start_date` (string): YYYY-MM-DD format
- `end_date` (string): YYYY-MM-DD format
- `marketplace` (string): Filter by marketplace
- `group_by` (string): day, week, month

---

## Database Schema

### Core Tables

#### meschain_marketplaces
```sql
CREATE TABLE `oc_meschain_marketplaces` (
    `marketplace_id` INT(11) NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `status` TINYINT(1) DEFAULT 1,
    `config` JSON,
    `date_added` DATETIME NOT NULL,
    `date_modified` DATETIME NOT NULL,
    PRIMARY KEY (`marketplace_id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### meschain_product_sync
```sql
CREATE TABLE `oc_meschain_product_sync` (
    `sync_id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `marketplace_id` INT(11) NOT NULL,
    `marketplace_product_id` VARCHAR(255),
    `sync_status` ENUM('pending','syncing','synced','error') DEFAULT 'pending',
    `last_sync` DATETIME,
    `sync_data` JSON,
    `error_message` TEXT,
    PRIMARY KEY (`sync_id`),
    KEY `product_marketplace` (`product_id`, `marketplace_id`),
    KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Security Guidelines

### API Security

1. **Always use HTTPS**
   - SSL certificate required
   - Enforce TLS 1.2 minimum

2. **API Key Management**
   - Store in encrypted format
   - Rotate keys regularly
   - Use environment variables

3. **Input Validation**
   ```php
   // Example validation
   $validator = new MeschainValidator();
   $validator->validate($data, [
       'product_id' => 'required|integer|min:1',
       'price' => 'required|numeric|min:0',
       'stock' => 'required|integer|min:0'
   ]);
   ```

4. **Rate Limiting**
   - Default: 1000 requests/hour per API key
   - Configurable per marketplace
   - Automatic blocking on abuse

### Data Protection

1. **Encryption at Rest**
   - Sensitive data encrypted using AES-256
   - Encryption keys stored in Azure Key Vault

2. **Encryption in Transit**
   - All API communications over HTTPS
   - Certificate pinning for critical endpoints

3. **Access Control**
   - Role-based permissions
   - IP whitelisting available
   - Multi-factor authentication support

---

## Performance Optimization

### Caching Strategy

1. **Query Caching**
   ```php
   $cache_key = 'products_' . md5(serialize($filters));
   if ($cached = $this->cache->get($cache_key)) {
       return $cached;
   }
   ```

2. **Object Caching**
   - Product data: 1 hour TTL
   - Category mappings: 24 hours TTL
   - API responses: 5 minutes TTL

3. **CDN Integration**
   - Static assets served via CDN
   - Image optimization automatic
   - Lazy loading implemented

### Database Optimization

1. **Indexes**
   ```sql
   -- Performance indexes
   CREATE INDEX idx_sync_status_date ON oc_meschain_product_sync(sync_status, last_sync);
   CREATE INDEX idx_marketplace_product ON oc_meschain_product_sync(marketplace_id, product_id);
   ```

2. **Query Optimization**
   - Use prepared statements
   - Batch operations where possible
   - Limit result sets appropriately

### Batch Processing

```php
// Process products in batches
$batch_size = 100;
$offset = 0;

while ($products = $this->getProductBatch($offset, $batch_size)) {
    $this->processBatch($products);
    $offset += $batch_size;

    // Prevent memory issues
    if ($offset % 1000 == 0) {
        $this->clearCache();
    }
}
```

---

## Troubleshooting

### Common Issues

#### 1. API Connection Errors

**Symptom:** "Failed to connect to marketplace API"

**Solutions:**
- Verify API credentials
- Check SSL certificate
- Ensure IP is whitelisted
- Test with curl:
  ```bash
  curl -X GET "https://api.marketplace.com/test" \
       -H "Authorization: Bearer YOUR_TOKEN"
  ```

#### 2. Sync Failures

**Symptom:** Products not syncing

**Solutions:**
- Check error logs: `/storage/logs/meschain/`
- Verify cron jobs are running
- Check database for stuck records:
  ```sql
  SELECT * FROM oc_meschain_product_sync
  WHERE sync_status = 'syncing'
  AND last_sync < DATE_SUB(NOW(), INTERVAL 1 HOUR);
  ```

#### 3. Performance Issues

**Symptom:** Slow dashboard loading

**Solutions:**
- Enable caching
- Optimize database queries
- Check server resources
- Run performance profiler:
  ```php
  $profiler = new MeschainProfiler();
  $profiler->start('dashboard_load');
  // ... code ...
  $profiler->end('dashboard_load');
  ```

### Debug Mode

Enable debug mode for detailed logging:

```php
// In config/meschain.php
'debug_mode' => true,
'log_level' => 'debug'
```

### Log Files

Log files are located in:
- General logs: `/storage/logs/meschain/app.log`
- Error logs: `/storage/logs/meschain/error.log`
- API logs: `/storage/logs/meschain/api.log`
- Performance logs: `/storage/logs/meschain/performance.log`

### Support

For additional support:
- Documentation: https://docs.meschain.com
- Support Portal: https://support.meschain.com
- Email: support@meschain.com
- Phone: +90 212 123 45 67

---

**End of Technical Documentation**
