# MesChain Trendyol Cron Job System Guide

## Day 5-6: Automatic Synchronization System

This guide covers the complete automated synchronization system for Trendyol integration, including cron jobs, webhook processing, background job queues, error handling, and performance monitoring.

## Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Cron Jobs](#cron-jobs)
6. [Webhook Processing](#webhook-processing)
7. [Admin Panel](#admin-panel)
8. [Monitoring & Logging](#monitoring--logging)
9. [Troubleshooting](#troubleshooting)
10. [Performance Optimization](#performance-optimization)

## Overview

The MesChain Trendyol Cron Job System provides:

- **Automated Product Synchronization**: Bulk upload and update products
- **Real-time Order Processing**: Automatic order import and status updates
- **Stock Level Management**: Bidirectional stock synchronization
- **Webhook Processing**: Real-time event handling with queue system
- **Error Handling & Retry Logic**: Robust failure recovery mechanisms
- **Performance Monitoring**: Statistics tracking and alerting
- **Admin Interface**: Complete management and monitoring dashboard

## System Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Trendyol API  │    │   Webhook URL   │    │   OpenCart DB   │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          ▼                      ▼                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Cron Job System                             │
├─────────────────┬─────────────────┬─────────────────┬─────────┤
│  Product Sync   │   Order Sync    │   Stock Sync    │Webhook  │
│                 │                 │                 │Processor│
├─────────────────┼─────────────────┼─────────────────┼─────────┤
│ • Bulk Upload   │ • Order Import  │ • Stock Updates │• Queue  │
│ • Product Update│ • Status Sync   │ • Price Sync    │• Retry  │
│ • Image Sync    │ • Tracking Info │ • Low Stock     │• Events │
│ • Attribute Sync│ • Return Process│ • History Track │• Real-  │
│                 │                 │                 │  time   │
└─────────────────┴─────────────────┴─────────────────┴─────────┘
          │                      │                      │
          ▼                      ▼                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Logging & Monitoring                        │
├─────────────────┬─────────────────┬─────────────────┬─────────┤
│   Sync Logs     │   Error Logs    │   Statistics    │ Alerts  │
│                 │                 │                 │         │
└─────────────────┴─────────────────┴─────────────────┴─────────┘
```

## Installation

### Automatic Installation (Recommended)

1. **Run the setup script:**
   ```bash
   sudo chmod +x scripts/setup_cron_jobs.sh
   sudo ./scripts/setup_cron_jobs.sh
   ```

2. **Custom installation options:**
   ```bash
   sudo ./scripts/setup_cron_jobs.sh \
     --php-path /usr/bin/php8.1 \
     --cron-user www-data \
     --log-dir /var/log/trendyol-cron
   ```

### Manual Installation

1. **Install database tables:**
   ```sql
   mysql -u root -p your_database < install/meschain_trendyol_install.sql
   ```

2. **Set up cron jobs:**
   ```bash
   crontab -e
   ```

   Add these lines:
   ```bash
   # MesChain Trendyol Cron Jobs
   */15 * * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/trendyol_sync.php
   0 * * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/product_sync.php
   */10 * * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/order_sync.php
   */30 * * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/stock_sync.php
   */5 * * * * /usr/bin/php /path/to/opencart/system/library/meschain/cron/webhook_processor.php
   ```

3. **Create log directory:**
   ```bash
   sudo mkdir -p /var/log/trendyol-cron
   sudo chown www-data:www-data /var/log/trendyol-cron
   ```

## Configuration

### Admin Panel Configuration

Navigate to: **Extensions > MesChain > Cron Management**

#### General Settings
- **Status**: Enable/disable the cron system
- **Product Sync**: Enable automatic product synchronization
- **Order Sync**: Enable automatic order synchronization
- **Stock Sync**: Enable automatic stock synchronization
- **Webhook Processing**: Enable real-time webhook processing

#### Sync Intervals
- **Product Sync Interval**: 60 minutes (recommended)
- **Order Sync Interval**: 15 minutes (recommended)
- **Stock Sync Interval**: 30 minutes (recommended)
- **Webhook Processing Interval**: 5 minutes (recommended)

#### Alert Settings
- **Alert Email**: Email address for error notifications
- **Alert on Error**: Send email alerts for sync errors
- **Alert on Low Stock**: Send email alerts for low stock items

#### Performance Settings
- **Batch Size**: Number of items to process per batch (50 recommended)
- **Max Execution Time**: Maximum time per sync process (300 seconds)
- **Memory Limit**: Memory limit for sync processes (256M recommended)

### Database Configuration

The system uses these main tables:

- `oc_trendyol_products`: Product sync status and data
- `oc_trendyol_orders`: Order sync status and data
- `oc_trendyol_sync_logs`: Sync execution logs
- `oc_trendyol_webhook_logs`: Webhook processing logs
- `oc_trendyol_sync_queue`: Background job queue
- `oc_trendyol_stock_history`: Stock change history
- `oc_trendyol_alerts`: System alerts and notifications

## Cron Jobs

### Main Synchronization (`trendyol_sync.php`)
- **Frequency**: Every 15 minutes
- **Purpose**: Orchestrates all sync operations
- **Features**:
  - Lock file management
  - Execution time limits
  - Comprehensive error handling
  - Statistics tracking

### Product Synchronization (`product_sync.php`)
- **Frequency**: Every hour
- **Purpose**: Sync products between OpenCart and Trendyol
- **Operations**:
  - New product uploads
  - Existing product updates
  - Image synchronization
  - Attribute mapping
  - Bulk operations for performance

### Order Synchronization (`order_sync.php`)
- **Frequency**: Every 10 minutes
- **Purpose**: Import and sync orders from Trendyol
- **Operations**:
  - New order import
  - Order status updates
  - Shipment tracking
  - Return processing
  - Customer data mapping

### Stock Synchronization (`stock_sync.php`)
- **Frequency**: Every 30 minutes
- **Purpose**: Sync stock levels and prices
- **Operations**:
  - Stock level updates
  - Price synchronization
  - Low stock alerts
  - Stock history tracking
  - Bidirectional sync

### Webhook Processor (`webhook_processor.php`)
- **Frequency**: Every 5 minutes
- **Purpose**: Process real-time webhook events
- **Features**:
  - Event prioritization
  - Queue management
  - Retry logic with exponential backoff
  - Event type handling
  - Real-time processing

## Webhook Processing

### Supported Events

| Event Type | Priority | Description |
|------------|----------|-------------|
| `ORDER_CREATED` | 1 | New order received |
| `ORDER_CANCELLED` | 1 | Order cancelled |
| `ORDER_STATUS_CHANGED` | 2 | Order status updated |
| `SHIPMENT_CREATED` | 2 | Shipment information |
| `RETURN_INITIATED` | 2 | Return request |
| `INVENTORY_UPDATED` | 3 | Stock level changed |
| `PRICE_UPDATED` | 3 | Price changed |
| `PRODUCT_APPROVED` | 4 | Product approved |
| `PRODUCT_REJECTED` | 4 | Product rejected |

### Queue Management

The webhook processor uses a sophisticated queue system:

1. **Priority-based processing**: High-priority events (orders) processed first
2. **Retry mechanism**: Failed webhooks retried with exponential backoff
3. **Error handling**: Comprehensive error logging and alerting
4. **Cleanup**: Automatic cleanup of old processed webhooks

### Webhook URL Setup

Configure your webhook URL in Trendyol Seller Panel:
```
https://yourdomain.com/index.php?route=extension/meschain/webhook/trendyol
```

## Admin Panel

### Dashboard Features

#### Monitoring Tab
- **Current Status**: Real-time status of all cron jobs
- **Today's Statistics**: Success/failure rates for today
- **Queue Status**: Pending items in each queue
- **Weekly Performance Chart**: Visual performance tracking

#### Logs Tab
- **Real-time Logs**: Live view of sync activities
- **Filtering**: Filter by sync type and status
- **Pagination**: Easy navigation through log history
- **Log Cleanup**: Automated old log cleanup

#### Setup Tab
- **Cron Script Generator**: Download automated setup script
- **Manual Setup Instructions**: Step-by-step manual setup
- **System Requirements**: PHP and server requirements
- **Troubleshooting Tips**: Common issues and solutions

### Manual Sync Controls

The admin panel provides manual sync buttons:
- **Sync Products**: Immediate product synchronization
- **Sync Orders**: Immediate order synchronization
- **Sync Stock**: Immediate stock synchronization
- **Process Webhooks**: Process pending webhooks
- **Full Sync**: Complete synchronization of all data

## Monitoring & Logging

### Log Files

All cron jobs generate detailed logs:

```
/var/log/trendyol-cron/
├── trendyol_sync.log      # Main sync orchestrator
├── product_sync.log       # Product synchronization
├── order_sync.log         # Order synchronization
├── stock_sync.log         # Stock synchronization
├── webhook_processor.log  # Webhook processing
└── monitor.log           # System monitoring
```

### Log Rotation

Automatic log rotation is configured:
- **Daily rotation**: Logs rotated daily
- **30-day retention**: Logs kept for 30 days
- **Compression**: Old logs compressed to save space

### Monitoring Script

The system includes a monitoring script (`/usr/local/bin/trendyol-cron-monitor`):
- **Log age checking**: Ensures logs are recent
- **Error detection**: Scans for recent errors
- **Automated alerts**: Email notifications for issues
- **Hourly execution**: Runs every hour automatically

### Performance Metrics

The system tracks comprehensive metrics:
- **Execution times**: How long each sync takes
- **Memory usage**: Memory consumption tracking
- **Success rates**: Percentage of successful operations
- **API call counts**: Number of API requests made
- **Error frequencies**: Types and frequency of errors

## Troubleshooting

### Common Issues

#### Cron Jobs Not Running
```bash
# Check if cron service is running
sudo systemctl status cron

# Check crontab entries
sudo crontab -u www-data -l

# Check log files for errors
sudo tail -f /var/log/trendyol-cron/trendyol_sync.log
```

#### PHP Errors
```bash
# Test PHP syntax
php -l /path/to/opencart/system/library/meschain/cron/trendyol_sync.php

# Check PHP version
php -v

# Test script execution
php /path/to/opencart/system/library/meschain/cron/trendyol_sync.php
```

#### Database Connection Issues
```bash
# Check database connectivity
mysql -u username -p database_name

# Verify table existence
SHOW TABLES LIKE 'oc_trendyol_%';

# Check table structure
DESCRIBE oc_trendyol_sync_logs;
```

#### API Connection Issues
- Verify API credentials in OpenCart admin
- Check API rate limits (600 calls/minute)
- Verify webhook URL accessibility
- Check firewall settings

### Debug Mode

Enable debug mode for detailed logging:
1. Go to admin panel: Extensions > MesChain > Trendyol Settings
2. Enable "Debug Mode"
3. Check detailed logs in `/var/log/trendyol-cron/`

### Performance Issues

If sync operations are slow:
1. **Reduce batch size**: Lower the batch size in admin panel
2. **Increase memory limit**: Adjust PHP memory limit
3. **Optimize database**: Run `OPTIMIZE TABLE` on sync tables
4. **Check server resources**: Monitor CPU and memory usage

## Performance Optimization

### Recommended Settings

For optimal performance, use these settings:

| Setting | Small Store (<1000 products) | Medium Store (1000-10000) | Large Store (>10000) |
|---------|------------------------------|---------------------------|---------------------|
| Batch Size | 25 | 50 | 100 |
| Memory Limit | 128M | 256M | 512M |
| Max Execution Time | 180s | 300s | 600s |
| Product Sync Interval | 30 min | 60 min | 120 min |
| Order Sync Interval | 10 min | 15 min | 15 min |
| Stock Sync Interval | 15 min | 30 min | 60 min |

### Database Optimization

Regular maintenance tasks:
```sql
-- Optimize sync tables
OPTIMIZE TABLE oc_trendyol_sync_logs;
OPTIMIZE TABLE oc_trendyol_webhook_logs;
OPTIMIZE TABLE oc_trendyol_products;
OPTIMIZE TABLE oc_trendyol_orders;

-- Clean old logs (older than 30 days)
DELETE FROM oc_trendyol_sync_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
DELETE FROM oc_trendyol_webhook_logs WHERE processed = 1 AND processed_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Server Optimization

- **PHP OPcache**: Enable PHP OPcache for better performance
- **MySQL Query Cache**: Enable MySQL query cache
- **SSD Storage**: Use SSD storage for better I/O performance
- **Adequate RAM**: Ensure sufficient RAM for concurrent processes

## API Rate Limiting

The system respects Trendyol API limits:
- **600 calls per minute**: Automatic rate limiting
- **36,000 calls per hour**: Hourly limit tracking
- **Exponential backoff**: Automatic retry with delays
- **Queue management**: Efficient API call distribution

## Security Considerations

- **Webhook validation**: All webhooks are validated for authenticity
- **SQL injection protection**: All database queries use prepared statements
- **File permissions**: Proper file permissions for cron scripts
- **Log security**: Sensitive data filtered from logs
- **API credential protection**: Secure storage of API credentials

## Support and Maintenance

### Regular Maintenance Tasks

1. **Weekly**: Review sync logs for errors
2. **Monthly**: Clean up old logs and optimize database
3. **Quarterly**: Review and update sync intervals based on performance
4. **As needed**: Update API credentials and webhook URLs

### Getting Help

For support with the cron system:
1. Check the troubleshooting section above
2. Review log files for specific error messages
3. Contact MesChain support with log excerpts
4. Use the admin panel's monitoring tools for diagnostics

---

**Version**: 1.0.0
**Compatible with**: OpenCart 4.0.2.3
**Last Updated**: Day 5-6 Implementation
**Author**: MesChain Development Team
