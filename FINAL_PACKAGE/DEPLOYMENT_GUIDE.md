# MesChain Trendyol Integration - Production Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the MesChain Trendyol Integration to a production OpenCart environment.

## Prerequisites

### System Requirements
- **PHP**: 7.4 or higher
- **OpenCart**: 4.x
- **MySQL**: 5.7 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for API communications
- **Composer**: Latest version
- **Git**: For version control

### PHP Extensions
- curl
- json
- mbstring
- pdo
- openssl
- zip
- xml

## Pre-Deployment Checklist

### 1. Environment Preparation
- [ ] Verify PHP version and extensions
- [ ] Install Composer
- [ ] Configure web server
- [ ] Set up SSL certificate
- [ ] Create database backup
- [ ] Prepare staging environment

### 2. Trendyol Account Setup
- [ ] Register as Trendyol seller
- [ ] Obtain API credentials
- [ ] Configure seller profile
- [ ] Set up product categories
- [ ] Test API access

### 3. OpenCart Preparation
- [ ] Update OpenCart to latest 4.x version
- [ ] Install required extensions
- [ ] Configure product attributes
- [ ] Set up tax classes
- [ ] Configure shipping methods

## Installation Steps

### Step 1: Download and Extract
```bash
# Download the package
wget https://releases.meschain.com/trendyol-integration-v1.0.0.zip

# Extract to temporary directory
unzip trendyol-integration-v1.0.0.zip -d /tmp/trendyol-integration

# Move to OpenCart directory
cd /path/to/opencart
```

### Step 2: Run Setup Script
```bash
# Make setup script executable
chmod +x /tmp/trendyol-integration/setup.sh

# Run setup
/tmp/trendyol-integration/setup.sh --production
```

### Step 3: Configure Environment
```bash
# Copy environment template
cp .env.example .env

# Edit configuration
nano .env
```

Required configuration:
```env
# Trendyol API Configuration
TRENDYOL_API_URL=https://api.trendyol.com
TRENDYOL_SUPPLIER_ID=your_supplier_id
TRENDYOL_API_KEY=your_api_key
TRENDYOL_API_SECRET=your_api_secret

# Database Configuration
DB_HOST=localhost
DB_NAME=opencart_production
DB_USER=opencart_user
DB_PASS=secure_password
DB_PREFIX=oc_

# OpenCart Configuration
OPENCART_URL=https://your-store.com
OPENCART_ADMIN_PATH=admin

# Production Settings
DEBUG_MODE=false
LOG_LEVEL=error
MONITORING_ENABLED=true
```

### Step 4: Install Dependencies
```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Set file permissions
chmod -R 755 system/
chmod -R 644 system/config/
chmod 600 .env
```

### Step 5: Database Setup
```bash
# Run database migrations
php system/cli/migrate.php

# Seed initial data
php system/cli/seed.php --production
```

### Step 6: Install OCMOD Package
```bash
# Generate OCMOD package
./build.sh --ocmod

# Install via OpenCart admin
# Admin → Extensions → Installer → Upload: trendyol-integration.ocmod.zip
```

### Step 7: Configure OpenCart Extension
1. Navigate to **Admin → Extensions → Extensions**
2. Filter by **Modules**
3. Find **Trendyol Integration**
4. Click **Install** then **Edit**
5. Configure settings:
   - API Credentials
   - Sync Settings
   - Product Mapping
   - Order Processing

### Step 8: Setup Monitoring
```bash
# Install monitoring system
./monitoring/setup_monitoring.sh --production

# Configure alerts
nano monitoring/config/alerts.json
```

### Step 9: Configure Cron Jobs
Add to crontab:
```bash
# Product sync every 30 minutes
*/30 * * * * /usr/bin/php /path/to/opencart/system/cli/trendyol_sync.php products

# Order sync every 5 minutes
*/5 * * * * /usr/bin/php /path/to/opencart/system/cli/trendyol_sync.php orders

# Stock sync every 15 minutes
*/15 * * * * /usr/bin/php /path/to/opencart/system/cli/trendyol_sync.php stock

# Health check every minute
* * * * * /path/to/opencart/deployment/health_check.sh

# Daily reports at 6 AM
0 6 * * * /usr/bin/php /path/to/opencart/system/cli/generate_reports.php
```

### Step 10: Security Configuration
```bash
# Set secure file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 .env
chmod 600 config.php
chmod 600 admin/config.php

# Configure firewall rules
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable

# Set up fail2ban
apt-get install fail2ban
systemctl enable fail2ban
```

## Post-Deployment Verification

### 1. Health Checks
```bash
# Run comprehensive health check
./deployment/health_check.sh --full

# Verify API connectivity
curl -H "Authorization: Bearer $API_TOKEN" \
     https://api.trendyol.com/sapigw/suppliers/$SUPPLIER_ID/products
```

### 2. Test Sync Operations
```bash
# Test product sync
php system/cli/trendyol_sync.php products --test

# Test order sync
php system/cli/trendyol_sync.php orders --test

# Test stock sync
php system/cli/trendyol_sync.php stock --test
```

### 3. Monitor Logs
```bash
# Check application logs
tail -f logs/trendyol.log

# Check error logs
tail -f logs/error.log

# Check sync logs
tail -f logs/sync.log
```

### 4. Performance Testing
```bash
# Run performance tests
composer test-performance

# Check response times
./tests/performance/benchmark.sh
```

## Monitoring and Maintenance

### Dashboard Access
- **URL**: `https://your-store.com/admin/trendyol/dashboard`
- **Monitoring**: `https://your-store.com/monitoring/dashboard`

### Key Metrics to Monitor
- API response times
- Sync success rates
- Error rates
- Database performance
- Server resources

### Regular Maintenance Tasks
- **Daily**: Check sync logs and error reports
- **Weekly**: Review performance metrics
- **Monthly**: Update dependencies and security patches
- **Quarterly**: Full system audit and optimization

## Troubleshooting

### Common Issues

#### API Connection Errors
```bash
# Check API credentials
php system/cli/test_api.php

# Verify SSL certificates
openssl s_client -connect api.trendyol.com:443
```

#### Sync Failures
```bash
# Check sync status
php system/cli/sync_status.php

# Reset sync state
php system/cli/reset_sync.php --confirm
```

#### Performance Issues
```bash
# Check database performance
php system/cli/db_check.php

# Optimize database
php system/cli/optimize.php
```

### Log Locations
- **Application**: `logs/trendyol.log`
- **Errors**: `logs/error.log`
- **Sync**: `logs/sync.log`
- **API**: `logs/api.log`
- **Performance**: `logs/performance.log`

## Rollback Procedure

If issues occur, use the automated rollback:
```bash
# Rollback to previous version
./deployment/rollback.sh

# Verify rollback
./deployment/health_check.sh
```

## Support and Documentation

### Resources
- **User Guide**: `docs/USER_GUIDE.md`
- **API Documentation**: `docs/API.md`
- **Troubleshooting**: `docs/TROUBLESHOOTING.md`

### Support Channels
- **Email**: support@meschain.com
- **Documentation**: https://docs.meschain.com/trendyol
- **GitHub Issues**: https://github.com/meschain/trendyol-integration

### Emergency Contacts
- **Technical Support**: +90 XXX XXX XXXX
- **Emergency Hotline**: +90 XXX XXX XXXX (24/7)

---

**Note**: This deployment guide assumes a standard production environment. Adjust configurations based on your specific infrastructure requirements.
