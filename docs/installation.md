# MesChain-Sync Installation Guide

## System Requirements

- OpenCart 3.x
- PHP 7.3 or higher
- MySQL 5.7 or higher
- cURL extension enabled
- OpenSSL extension enabled
- ZIP extension enabled

## Installation Steps

### 1. Prepare Your OpenCart Store

Before installing MesChain-Sync, ensure your OpenCart store is properly set up:

- Make sure you have a complete backup of your store
- Temporarily disable third-party extensions to avoid conflicts
- Set PHP memory limit to at least 128MB

### 2. Upload Files

1. Download the MesChain-Sync extension package
2. Unzip the package to a local folder
3. Upload all files from the `upload` folder to your OpenCart root directory
   - Maintain the folder structure during upload
   - Ensure file permissions are properly set (755 for directories, 644 for files)

### 3. Install the Extension

1. Log in to your OpenCart admin panel
2. Navigate to Extensions > Installer
3. Upload the `meschain_sync.ocmod.xml` file
4. Navigate to Extensions > Modifications
5. Click the Refresh button to apply the modifications

### 4. Enable the Extension

1. Navigate to Extensions > Extensions
2. Select "Modules" from the extension type dropdown
3. Find "MesChain-Sync" in the list
4. Click the Install button
5. Once installed, click the Edit button to configure

### 5. Configure Marketplace Integrations

After installation, you'll need to configure each marketplace integration:

#### N11 Integration

1. Navigate to MesChain-Sync > N11 in the left menu
2. Enter your N11 API credentials:
   - App Key
   - App Secret
3. Click "Test Connection" to verify your credentials
4. Save settings

#### Trendyol Integration

1. Navigate to MesChain-Sync > Trendyol in the left menu
2. Enter your Trendyol API credentials:
   - Supplier ID
   - API Key
   - API Secret
3. Test the connection
4. Save settings

#### Amazon Integration

1. Navigate to MesChain-Sync > Amazon in the left menu
2. Enter your Amazon API credentials:
   - Seller ID
   - MWS Auth Token
   - AWS Access Key
   - AWS Secret Key
   - Marketplace ID
3. Test the connection
4. Save settings

### 6. Set Up Cron Jobs

For automatic synchronization, set up the following cron jobs:

```bash
# Sync products every 6 hours
0 */6 * * * php /path/to/your/opencart/system/library/entegrator/cron/sync_products.php all

# Sync orders every 30 minutes
*/30 * * * * php /path/to/your/opencart/system/library/entegrator/cron/sync_orders.php all

# Sync stock every hour
0 * * * * php /path/to/your/opencart/system/library/entegrator/cron/sync_stock.php all
```

Replace `/path/to/your/opencart` with the actual path to your OpenCart installation.

### 7. Test the Installation

After installation and configuration, test the following:

1. Access the MesChain-Sync dashboard
2. Manually sync a few products to each marketplace
3. Check if the sync was successful
4. Verify that product information is correctly displayed on marketplaces
5. Test order synchronization

### 8. Troubleshooting

If you encounter issues during installation or configuration:

1. Check the OpenCart error logs (System > Maintenance > Error Logs)
2. Check the MesChain-Sync logs (MesChain-Sync > Logs)
3. Verify API credentials for each marketplace
4. Ensure your server has internet access to connect to marketplace APIs
5. Check server PHP and MySQL versions meet requirements

### 9. Update Process

To update MesChain-Sync to a newer version:

1. Backup your existing OpenCart store
2. Upload the new files from the `upload` folder
3. Navigate to Extensions > Modifications
4. Click the Refresh button
5. Check the extension settings to ensure they are still correct

### 10. Support

For additional support:

- Visit the documentation (MesChain-Sync > Help)
- Contact MesTech support at support@mestech.com
- Visit our support portal at https://support.mestech.com 