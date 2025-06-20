# MESCHAIN-SYNC ENTERPRISE DEPLOYMENT GUIDE

**Version:** 3.0.0
**Platform:** OpenCart 4.0.2.3
**Last Updated:** June 18, 2025

## Quick Start

### System Requirements
- PHP 7.4+ (Recommended: 8.0+)
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx with SSL
- 2GB+ RAM, 500MB disk space

### Installation Steps
1. **Upload OCMOD file** via Extensions → Extension Installer
2. **Install extension** from Extensions → Modules
3. **Configure marketplaces** in MesChain Sync settings
4. **Set up cron jobs** for automation
5. **Test connections** and run first sync

### Cron Jobs
```bash
# Add to crontab -e
*/5 * * * * php /path/to/opencart/meschain-cron.php sync-products
*/2 * * * * php /path/to/opencart/meschain-cron.php import-orders
*/10 * * * * php /path/to/opencart/meschain-cron.php sync-inventory
```

### Health Check
Test your installation: `https://yourdomain.com/health-check.php`

For complete deployment instructions, see the full documentation at: https://docs.meschain.com/deployment

## System Requirements

### Minimum Requirements
- **PHP:** 7.4+ (Recommended: 8.0+)
- **MySQL:** 5.7+ or MariaDB 10.3+
- **Web Server:** Apache 2.4+ or Nginx 1.16+
- **Memory:** 512MB (Recommended: 2GB+)
- **Disk Space:** 500MB free space
- **SSL Certificate:** Required for marketplace integrations

### Recommended Requirements
- **PHP:** 8.1+ with OPcache enabled
- **MySQL:** 8.0+ or MariaDB 10.6+
- **Memory:** 4GB+ for high-volume operations
- **SSD Storage:** For better performance
- **CDN:** For static assets (optional)

### Required PHP Extensions
```bash
# Check required extensions
php -m | grep -E "(curl|json|mbstring|openssl|pdo|pdo_mysql|zip|gd|intl)"
```

Required extensions:
- curl (for API communications)
- json (for data processing)
- mbstring (for string handling)
- openssl (for encryption)
- pdo & pdo_mysql (for database)
- zip (for file operations)
- gd (for image processing)
- intl (for internationalization)

---

## Pre-Installation Checklist

### 1. OpenCart Verification
```bash
# Verify OpenCart version
grep -r "VERSION" catalog/controller/startup/startup.php
# Should show 4.0.2.3
```

### 2. File Permissions
```bash
# Set correct permissions
chmod 755 system/storage/
chmod 755 system/storage/logs/
chmod 755 system/storage/cache/
chmod 755 system/storage/session/
chmod 755 system/storage/upload/
chmod 644 config.php
chmod 644 admin/config.php
```

### 3. Database Backup
```bash
# Create backup before installation
mysqldump -u username -p database_name > backup_before_meschain.sql
```

---

## Installation Methods

### Method 1: OCMOD Installation (Recommended)

#### Step 1: Upload Extension
1. Download `meschain-sync-enterprise-v3.0.0.ocmod.zip`
2. Login to OpenCart Admin Panel
3. Navigate to **Extensions → Extension Installer**
4. Click **Upload** and select the OCMOD file
5. Wait for installation to complete

#### Step 2: Install Extension
1. Go to **Extensions → Extensions**
2. Choose **Modules** from the dropdown
3. Find **MesChain Sync Enterprise**
4. Click **Install** (green plus icon)
5. Click **Edit** to configure

#### Step 3: Refresh Modifications
1. Navigate to **Extensions → Modifications**
2. Click **Refresh** (blue refresh button)
3. Clear all caches:
   - Go to **Dashboard → Settings → Developer**
   - Clear Theme Cache, SASS Cache, and Image Cache

### Method 2: Manual Installation

#### Step 1: Extract Files
```bash
# Extract the package
unzip meschain-sync-enterprise-v3.0.0.zip

# Copy files to OpenCart directory
cp -r upload/* /path/to/your/opencart/
```

#### Step 2: Set Permissions
```bash
# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 644 config.php admin/config.php
```

#### Step 3: Database Installation
```bash
# Import database schema
mysql -u username -p database_name < sql/install.sql
```

---

## Configuration

### 1. Basic Configuration

#### Admin Panel Setup
1. Navigate to **MesChain Sync → Settings**
2. Configure basic settings:
   - **System Language:** Turkish/English
   - **Default Currency:** TRY/USD/EUR
   - **Time Zone:** Europe/Istanbul
   - **Log Level:** Info (Production) / Debug (Testing)

#### Database Optimization
```sql
-- Run these queries for better performance
CREATE INDEX idx_meschain_product_sync ON oc_meschain_product_sync (marketplace, sync_status);
CREATE INDEX idx_meschain_order_integration ON oc_meschain_order_integration (marketplace, date_integrated);
OPTIMIZE TABLE oc_meschain_product_sync;
OPTIMIZE TABLE oc_meschain_order_integration;
```

### 2. Marketplace Configuration

#### Trendyol Setup
1. **MesChain Sync → Marketplaces → Trendyol**
2. Enter credentials:
   - **API Key:** Your Trendyol API key
   - **API Secret:** Your Trendyol API secret
   - **Supplier ID:** Your supplier ID
3. **Test Connection** → Should show "Success"

#### Hepsiburada Setup
1. **MesChain Sync → Marketplaces → Hepsiburada**
2. Enter credentials:
   - **Username:** Your merchant username
   - **Password:** Your merchant password
   - **Merchant ID:** Your merchant ID
3. **Test Connection** → Should show "Success"

#### Amazon Setup (Global)
1. **MesChain Sync → Marketplaces → Amazon**
2. Enter SP-API credentials:
   - **Access Key:** Your AWS access key
   - **Secret Key:** Your AWS secret key
   - **Role ARN:** Your Amazon role ARN
   - **Client ID:** Your SP-API client ID
   - **Client Secret:** Your SP-API client secret
   - **Refresh Token:** Your refresh token
3. Select marketplaces: (US, UK, DE, FR, etc.)

### 3. Automation Setup

#### Cron Jobs
Add these to your crontab:

```bash
# Edit crontab
crontab -e

# Add these lines:
# Product sync every 5 minutes
*/5 * * * * /usr/bin/php /path/to/opencart/meschain-cron.php sync-products

# Order import every 2 minutes
*/2 * * * * /usr/bin/php /path/to/opencart/meschain-cron.php import-orders

# Inventory sync every 10 minutes
*/10 * * * * /usr/bin/php /path/to/opencart/meschain-cron.php sync-inventory

# Daily cleanup and reports
0 2 * * * /usr/bin/php /path/to/opencart/meschain-cron.php daily-cleanup

# Weekly analytics
0 1 * * 0 /usr/bin/php /path/to/opencart/meschain-cron.php weekly-analytics
```

#### Queue Worker Setup
```bash
# Create systemd service for queue worker
sudo nano /etc/systemd/system/meschain-queue.service
```

```ini
[Unit]
Description=MesChain Sync Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/opencart
ExecStart=/usr/bin/php meschain-queue-worker.php
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start the service
sudo systemctl enable meschain-queue.service
sudo systemctl start meschain-queue.service
```

---

## Security Configuration

### 1. SSL/TLS Setup
```apache
# Apache SSL configuration (.htaccess)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### 2. API Security
```php
// config.php additions for API security
define('MESCHAIN_API_RATE_LIMIT', 1000); // Requests per hour
define('MESCHAIN_API_WHITELIST', '127.0.0.1,10.0.0.0/8'); // Allowed IPs
define('MESCHAIN_ENCRYPTION_KEY', 'your-256-bit-key-here'); // Generate unique key
```

### 3. Database Security
```sql
-- Create dedicated database user for MesChain
CREATE USER 'meschain_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON opencart_db.oc_meschain_* TO 'meschain_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## Performance Optimization

### 1. PHP Configuration
```ini
# php.ini optimizations
memory_limit = 2048M
max_execution_time = 300
max_input_time = 300
post_max_size = 100M
upload_max_filesize = 100M

# OPcache settings
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.revalidate_freq = 2
```

### 2. MySQL Optimization
```ini
# my.cnf optimizations
[mysql]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 256M
tmp_table_size = 256M
max_heap_table_size = 256M
```

### 3. Web Server Optimization

#### Apache Configuration
```apache
# .htaccess optimizations
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

#### Nginx Configuration
```nginx
# nginx.conf optimizations
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

location ~* \.(json|xml)$ {
    expires 1h;
    add_header Cache-Control "public";
}

# Gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
```

---

## Monitoring and Logging

### 1. Log Configuration
```php
// Set up detailed logging
define('MESCHAIN_LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR
define('MESCHAIN_LOG_FILE', DIR_LOGS . 'meschain.log');
define('MESCHAIN_MAX_LOG_SIZE', 50 * 1024 * 1024); // 50MB
```

### 2. Health Check Endpoint
Create `/health-check.php`:
```php
<?php
// Simple health check
$status = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '3.0.0',
    'database' => 'connected',
    'marketplaces' => []
];

// Check database
try {
    $pdo = new PDO($dsn, $username, $password);
    $status['database'] = 'connected';
} catch (Exception $e) {
    $status['database'] = 'error';
    $status['status'] = 'unhealthy';
}

// Check marketplace APIs
$marketplaces = ['trendyol', 'hepsiburada', 'amazon', 'n11'];
foreach ($marketplaces as $marketplace) {
    $status['marketplaces'][$marketplace] = checkMarketplaceHealth($marketplace);
}

header('Content-Type: application/json');
echo json_encode($status, JSON_PRETTY_PRINT);
?>
```

### 3. Performance Monitoring
```bash
# Create monitoring script
#!/bin/bash
# monitor-meschain.sh

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.2f", $3/$2 * 100.0}')

# Check CPU usage
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | awk -F'%' '{print $1}')

# Check disk usage
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')

# Log metrics
echo "$(date): Memory: ${MEMORY_USAGE}%, CPU: ${CPU_USAGE}%, Disk: ${DISK_USAGE}%" >> /var/log/meschain-metrics.log

# Alert if thresholds exceeded
if (( $(echo "$MEMORY_USAGE > 80" | bc -l) )); then
    echo "ALERT: High memory usage: ${MEMORY_USAGE}%" | mail -s "MesChain Alert" admin@yourdomain.com
fi
```

---

## Backup and Recovery

### 1. Automated Backup Script
```bash
#!/bin/bash
# backup-meschain.sh

BACKUP_DIR="/backups/meschain"
DATE=$(date +%Y%m%d_%H%M%S)
MYSQL_USER="backup_user"
MYSQL_PASS="backup_password"
DB_NAME="opencart_db"

# Create backup directory
mkdir -p $BACKUP_DIR/$DATE

# Backup database
mysqldump -u $MYSQL_USER -p$MYSQL_PASS $DB_NAME > $BACKUP_DIR/$DATE/database.sql

# Backup files
tar -czf $BACKUP_DIR/$DATE/files.tar.gz upload/system/library/meschain upload/admin/controller/extension/module/meschain_*

# Backup configuration
cp config.php $BACKUP_DIR/$DATE/
cp admin/config.php $BACKUP_DIR/$DATE/admin_config.php

# Remove old backups (keep 30 days)
find $BACKUP_DIR -type d -mtime +30 -exec rm -rf {} \;

echo "Backup completed: $BACKUP_DIR/$DATE"
```

### 2. Recovery Procedures
```bash
# Recovery script
#!/bin/bash
# restore-meschain.sh

BACKUP_DATE=$1
BACKUP_DIR="/backups/meschain/$BACKUP_DATE"

if [ -z "$BACKUP_DATE" ]; then
    echo "Usage: $0 <backup_date>"
    echo "Available backups:"
    ls -1 /backups/meschain/
    exit 1
fi

# Stop services
systemctl stop apache2
systemctl stop meschain-queue

# Restore database
mysql -u root -p opencart_db < $BACKUP_DIR/database.sql

# Restore files
cd /var/www/html/opencart
tar -xzf $BACKUP_DIR/files.tar.gz

# Restore configuration
cp $BACKUP_DIR/config.php .
cp $BACKUP_DIR/admin_config.php admin/config.php

# Set permissions
chown -R www-data:www-data .
chmod -R 755 system/storage/

# Start services
systemctl start apache2
systemctl start meschain-queue

echo "Recovery completed from backup: $BACKUP_DATE"
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. Extension Not Installing
**Error:** "Permission denied" or "Cannot write to directory"
**Solution:**
```bash
# Fix permissions
chmod 755 system/storage/modification/
chown -R www-data:www-data system/storage/
```

#### 2. Marketplace Connection Failures
**Error:** "API connection failed"
**Solutions:**
- Check API credentials in marketplace panel
- Verify SSL certificate is valid
- Check firewall/IP whitelist settings
- Test with curl:
```bash
curl -I https://api.trendyol.com/sapigw/suppliers
```

#### 3. Cron Jobs Not Running
**Error:** Products not syncing automatically
**Solutions:**
```bash
# Check cron service
systemctl status cron

# Test cron job manually
/usr/bin/php /path/to/opencart/meschain-cron.php sync-products

# Check cron logs
tail -f /var/log/cron.log
```

#### 4. Performance Issues
**Symptoms:** Slow response times, timeouts
**Solutions:**
- Check MySQL slow query log
- Optimize database tables
- Increase PHP memory limit
- Enable OPcache
- Check server resources

#### 5. Memory Exhaustion
**Error:** "Fatal error: Allowed memory size exhausted"
**Solutions:**
```php
// Increase memory limit temporarily
ini_set('memory_limit', '2048M');

// Or in php.ini
memory_limit = 2048M
```

### Log File Locations
- **MesChain Logs:** `system/storage/logs/meschain.log`
- **OpenCart Logs:** `system/storage/logs/error.log`
- **Apache Logs:** `/var/log/apache2/error.log`
- **Nginx Logs:** `/var/log/nginx/error.log`
- **MySQL Logs:** `/var/log/mysql/error.log`

---

## Post-Deployment Checklist

### ✅ Functional Testing
- [ ] All marketplace connections working
- [ ] Product sync functioning
- [ ] Order import working
- [ ] Inventory updates real-time
- [ ] Price synchronization active
- [ ] Reports generating correctly

### ✅ Security Testing
- [ ] SSL certificate valid
- [ ] API endpoints secured
- [ ] Admin panel protected
- [ ] Database access restricted
- [ ] File permissions correct

### ✅ Performance Testing
- [ ] Page load times < 3 seconds
- [ ] API response times < 1 second
- [ ] Bulk operations completing
- [ ] Memory usage stable
- [ ] No memory leaks

### ✅ Monitoring Setup
- [ ] Health check endpoint working
- [ ] Log rotation configured
- [ ] Backup schedule active
- [ ] Alert system configured
- [ ] Performance monitoring enabled

---

## Support and Maintenance

### Maintenance Schedule
- **Daily:** Check logs, monitor performance
- **Weekly:** Review backup integrity, update statistics
- **Monthly:** Security patches, performance optimization
- **Quarterly:** Full system review, capacity planning

### Getting Support
- **Documentation:** https://docs.meschain.com
- **Support Email:** support@meschain.com
- **Emergency Hotline:** +90 212 123 45 67
- **Community Forum:** https://forum.meschain.com

### Update Procedures
1. Always backup before updates
2. Test updates on staging environment
3. Follow semantic versioning guidelines
4. Review changelog carefully
5. Monitor system after updates

---

**Deployment Guide Version:** 3.0.0
**Last Updated:** June 18, 2025
**Next Review:** September 18, 2025
