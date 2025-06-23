# MesChain Trendyol Integration - User Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Product Management](#product-management)
5. [Order Management](#order-management)
6. [Monitoring & Troubleshooting](#monitoring--troubleshooting)
7. [Advanced Features](#advanced-features)
8. [FAQ](#faq)

## Introduction

MesChain Trendyol Integration is a comprehensive OpenCart extension that seamlessly connects your e-commerce store with Trendyol marketplace. This integration enables automated product synchronization, order management, inventory updates, and real-time monitoring.

### Key Features
- **Complete API Integration**: Full Trendyol API connectivity
- **Automated Synchronization**: Products, orders, and inventory sync
- **Real-time Monitoring**: System health and performance tracking
- **E-Invoice Support**: Automated invoice generation and processing
- **Barcode Generation**: Product barcode creation and management
- **Webhook Processing**: Real-time event handling
- **Comprehensive Logging**: Detailed activity and error logging

## Installation

### System Requirements
- OpenCart 4.0.0 or higher
- PHP 8.0 or higher
- MySQL 5.7 or higher
- cURL extension enabled
- JSON extension enabled
- GD extension (for barcode generation)

### Automated Installation

1. **Download the Package**
   ```bash
   wget https://releases.meschain.com/trendyol-integration-v1.0.0.zip
   unzip trendyol-integration-v1.0.0.zip
   cd meschain-trendyol-integration
   ```

2. **Run the Deployment Script**
   ```bash
   chmod +x deployment/deploy.sh
   ./deployment/deploy.sh
   ```

3. **Follow the Installation Wizard**
   - The script will guide you through the installation process
   - Provide your OpenCart directory path
   - Enter your database credentials
   - Configure Trendyol API settings

### Manual Installation

1. **Upload Files**
   - Upload the extension files to your OpenCart directory
   - Ensure proper file permissions (644 for files, 755 for directories)

2. **Install Database Schema**
   ```bash
   mysql -u username -p database_name < install/meschain_trendyol_install.sql
   ```

3. **Configure Cron Jobs**
   ```bash
   chmod +x scripts/setup_cron_jobs.sh
   ./scripts/setup_cron_jobs.sh
   ```

## Configuration

### Basic Configuration

1. **Access Admin Panel**
   - Navigate to Extensions → MesChain → Trendyol Integration

2. **API Configuration**
   - **API Key**: Your Trendyol API key
   - **API Secret**: Your Trendyol API secret
   - **Supplier ID**: Your Trendyol supplier ID
   - **Sandbox Mode**: Enable for testing (disable in production)

3. **General Settings**
   - **Extension Status**: Enable/disable the extension
   - **Debug Mode**: Enable detailed logging
   - **Auto Sync**: Enable automatic synchronization
   - **Sync Interval**: Set synchronization frequency

### Advanced Configuration

#### Product Sync Settings
```php
// Product sync configuration
$config['meschain_trendyol_product_sync'] = [
    'batch_size' => 50,
    'sync_images' => true,
    'sync_categories' => true,
    'auto_approve' => false,
    'price_margin' => 0, // Percentage markup
    'stock_buffer' => 5  // Safety stock buffer
];
```

#### Order Processing Settings
```php
// Order processing configuration
$config['meschain_trendyol_order_processing'] = [
    'auto_import' => true,
    'default_status' => 'Processing',
    'create_customer' => true,
    'send_notifications' => true,
    'invoice_generation' => true
];
```

## Product Management

### Product Synchronization

#### Manual Sync
1. Navigate to **Catalog → Products**
2. Select products to sync
3. Click **Actions → Sync to Trendyol**
4. Monitor sync status in the dashboard

#### Automatic Sync
- Products are automatically synced based on configured intervals
- New products are queued for sync upon creation
- Price and stock updates trigger immediate sync

#### Sync Status Monitoring
```php
// Check product sync status
$product_sync_status = $this->model_extension_meschain_trendyol->getProductSyncStatus($product_id);

switch ($product_sync_status) {
    case 'pending':
        // Product queued for sync
        break;
    case 'syncing':
        // Sync in progress
        break;
    case 'synced':
        // Successfully synced
        break;
    case 'failed':
        // Sync failed - check logs
        break;
}
```

### Product Categories

#### Category Mapping
1. Navigate to **Extensions → MesChain → Category Mapping**
2. Map OpenCart categories to Trendyol categories
3. Set category-specific settings:
   - Commission rates
   - Shipping templates
   - Return policies

#### Bulk Category Assignment
```php
// Bulk category mapping
$category_mappings = [
    'opencart_category_id' => 'trendyol_category_id',
    '25' => '411', // Electronics
    '57' => '469', // Clothing
    '34' => '322'  // Home & Garden
];

$this->model_extension_meschain_trendyol->bulkUpdateCategoryMappings($category_mappings);
```

### Product Attributes

#### Required Attributes
- **Title**: Product name (max 100 characters)
- **Description**: Product description (max 3000 characters)
- **Brand**: Product brand (must be approved by Trendyol)
- **Barcode**: Product barcode (EAN-13 or UPC)
- **Price**: Product price (including VAT)
- **Stock**: Available quantity

#### Optional Attributes
- **Images**: Product images (max 8 images, 2MB each)
- **Variants**: Size, color, and other variations
- **Dimensions**: Weight, width, height, length
- **Warranty**: Warranty period and terms

## Order Management

### Order Import

#### Automatic Import
- Orders are automatically imported via webhooks
- Import frequency: Real-time (webhooks) + scheduled backup (every 15 minutes)
- Order statuses are synchronized bidirectionally

#### Manual Import
```php
// Manual order import
$order_importer = new TrendyolOrderImporter();
$imported_orders = $order_importer->importOrders([
    'start_date' => '2025-06-01',
    'end_date' => '2025-06-21',
    'status' => 'Created'
]);
```

### Order Processing Workflow

1. **Order Received**
   - Order imported from Trendyol
   - Customer created (if not exists)
   - Order status: "Processing"

2. **Order Confirmation**
   - Inventory check and reservation
   - Payment verification
   - Order status: "Confirmed"

3. **Shipping Preparation**
   - Pick and pack process
   - Shipping label generation
   - Order status: "Shipped"

4. **Delivery Tracking**
   - Tracking information updated
   - Customer notifications sent
   - Order status: "Delivered"

### Order Status Mapping

| Trendyol Status | OpenCart Status | Description |
|----------------|-----------------|-------------|
| Created | Processing | Order received |
| Approved | Confirmed | Order approved |
| Picking | Preparing | Being prepared |
| Invoiced | Invoiced | Invoice generated |
| Shipped | Shipped | Package dispatched |
| Delivered | Complete | Order delivered |
| Cancelled | Cancelled | Order cancelled |
| Returned | Returned | Order returned |

## Monitoring & Troubleshooting

### System Health Dashboard

Access the monitoring dashboard at:
```
https://yourstore.com/admin/index.php?route=extension/meschain/trendyol/dashboard
```

#### Key Metrics
- **System Health**: CPU, memory, disk usage
- **API Performance**: Response times, rate limits
- **Sync Statistics**: Success rates, error counts
- **Order Processing**: Processing times, queue status

### Log Management

#### Log Locations
```bash
# Application logs
/var/log/meschain-trendyol/application.log

# API logs
/var/log/meschain-trendyol/api.log

# Sync logs
/var/log/meschain-trendyol/sync.log

# Error logs
/var/log/meschain-trendyol/error.log
```

#### Log Analysis
```bash
# View recent errors
tail -f /var/log/meschain-trendyol/error.log

# Search for specific issues
grep "API_ERROR" /var/log/meschain-trendyol/api.log

# Monitor sync activity
tail -f /var/log/meschain-trendyol/sync.log | grep "PRODUCT_SYNC"
```

### Common Issues & Solutions

#### API Connection Issues
```bash
# Test API connectivity
php scripts/test_api_connection.php

# Check API credentials
php scripts/validate_credentials.php
```

**Solution**: Verify API credentials and network connectivity

#### Sync Failures
```bash
# Check sync queue
php scripts/check_sync_queue.php

# Retry failed syncs
php scripts/retry_failed_syncs.php
```

**Solution**: Review error logs and retry failed operations

#### Performance Issues
```bash
# Run performance diagnostics
php scripts/performance_check.php

# Optimize database
php scripts/optimize_database.php
```

**Solution**: Optimize database queries and increase system resources

### Health Check Script

```bash
#!/bin/bash
# Quick health check
./deployment/health_check.sh

# Detailed system analysis
./deployment/health_check.sh --detailed

# Generate health report
./deployment/health_check.sh --report
```

## Advanced Features

### Webhook Configuration

#### Setting Up Webhooks
1. Configure webhook URL in Trendyol seller panel:
   ```
   https://yourstore.com/index.php?route=extension/meschain/trendyol/webhook
   ```

2. Supported webhook events:
   - Order status changes
   - Product approvals/rejections
   - Inventory updates
   - Return requests

#### Webhook Security
```php
// Webhook signature verification
$webhook_signature = $_SERVER['HTTP_X_TRENDYOL_SIGNATURE'];
$payload = file_get_contents('php://input');
$expected_signature = hash_hmac('sha256', $payload, $webhook_secret);

if (!hash_equals($webhook_signature, $expected_signature)) {
    http_response_code(401);
    exit('Unauthorized');
}
```

### Custom Integrations

#### API Extensions
```php
// Custom API endpoint
class CustomTrendyolApi extends TrendyolApiClient
{
    public function customProductSync($products)
    {
        // Custom sync logic
        foreach ($products as $product) {
            $this->syncProductWithCustomLogic($product);
        }
    }

    private function syncProductWithCustomLogic($product)
    {
        // Implement custom business logic
        $enhanced_product = $this->enhanceProductData($product);
        return $this->syncProduct($enhanced_product);
    }
}
```

#### Event Hooks
```php
// Register custom event handlers
$this->event->register('meschain.trendyol.product.before_sync', function($product) {
    // Custom pre-sync processing
    return $this->customPreSyncProcessing($product);
});

$this->event->register('meschain.trendyol.order.after_import', function($order) {
    // Custom post-import processing
    $this->customPostImportProcessing($order);
});
```

### Performance Optimization

#### Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_trendyol_product_sync ON oc_trendyol_products (sync_status, last_sync);
CREATE INDEX idx_trendyol_order_status ON oc_trendyol_orders (status, created_at);
CREATE INDEX idx_trendyol_sync_queue ON oc_trendyol_sync_queue (status, priority, created_at);
```

#### Caching Configuration
```php
// Redis cache configuration
$config['meschain_trendyol_cache'] = [
    'driver' => 'redis',
    'host' => '127.0.0.1',
    'port' => 6379,
    'ttl' => 3600, // 1 hour
    'prefix' => 'meschain_trendyol:'
];
```

## FAQ

### General Questions

**Q: What is the minimum OpenCart version required?**
A: OpenCart 4.0.0 or higher is required for full compatibility.

**Q: Can I use this extension with multiple Trendyol accounts?**
A: Currently, the extension supports one Trendyol account per OpenCart installation.

**Q: Is there a limit on the number of products I can sync?**
A: There's no hard limit, but performance may vary based on server resources and Trendyol API limits.

### Technical Questions

**Q: How often are products synchronized?**
A: Products are synchronized every 15 minutes by default, with immediate sync for critical updates.

**Q: What happens if the API is temporarily unavailable?**
A: The system implements retry logic with exponential backoff. Failed operations are queued for retry.

**Q: Can I customize the product mapping logic?**
A: Yes, the extension provides hooks and events for custom mapping logic.

### Troubleshooting

**Q: Why are my products not appearing on Trendyol?**
A: Check the sync status in the dashboard. Products may be pending approval or have validation errors.

**Q: Orders are not importing automatically. What should I check?**
A: Verify webhook configuration and check the webhook logs for any errors.

**Q: The extension is consuming too much server resources. How can I optimize it?**
A: Adjust batch sizes, enable caching, and consider upgrading server resources.

## Support

### Documentation
- [API Reference](API_REFERENCE.md)
- [Developer Guide](DEVELOPER_GUIDE.md)
- [Troubleshooting Guide](TROUBLESHOOTING.md)

### Contact Information
- **Technical Support**: support@meschain.com
- **Sales Inquiries**: sales@meschain.com
- **Documentation**: docs.meschain.com

### Community
- **GitHub**: https://github.com/meschain/trendyol-integration
- **Forum**: https://community.meschain.com
- **Discord**: https://discord.gg/meschain

---

**Version**: 1.0.0
**Last Updated**: June 21, 2025
**License**: Commercial License
