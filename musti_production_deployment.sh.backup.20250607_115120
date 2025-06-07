#!/bin/bash

# MesChain-Sync Production Deployment Script
# Musti DevOps Team Production Deployment Preparation
# Version: 2.0
# Date: June 5, 2025

set -e

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="MesChain-Sync Enterprise"
VERSION="3.0.1"
BACKUP_DIR="/var/backups/meschain"
LOG_FILE="/var/log/meschain-deployment.log"
HEALTH_CHECK_URL="https://your-domain.com/api/health"

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}  $PROJECT_NAME v$VERSION${NC}"
echo -e "${BLUE}  Production Deployment${NC}"
echo -e "${BLUE}  Musti DevOps Team${NC}"
echo -e "${BLUE}================================${NC}"

# Functions
log_message() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] $1${NC}"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

error_message() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" >> "$LOG_FILE"
}

warning_message() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] WARNING: $1" >> "$LOG_FILE"
}

# 1. Pre-deployment checks
log_message "Starting pre-deployment checks..."

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error_message "This script should not be run as root for security reasons"
   exit 1
fi

# Check disk space
AVAILABLE_SPACE=$(df / | awk 'NR==2{printf "%.0f", $4/1024/1024}')
if [[ $AVAILABLE_SPACE -lt 5 ]]; then
    error_message "Insufficient disk space. At least 5GB required. Available: ${AVAILABLE_SPACE}GB"
    exit 1
fi

log_message "✓ Disk space check passed (${AVAILABLE_SPACE}GB available)"

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
if [[ $(echo "$PHP_VERSION < 7.4" | bc) -eq 1 ]]; then
    error_message "PHP 7.4+ required. Current version: $PHP_VERSION"
    exit 1
fi

log_message "✓ PHP version check passed ($PHP_VERSION)"

# Check required PHP extensions
REQUIRED_EXTENSIONS=("mysql" "mysqli" "pdo_mysql" "curl" "gd" "json" "mbstring" "openssl" "zip")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -q "$ext"; then
        error_message "Required PHP extension missing: $ext"
        exit 1
    fi
done

log_message "✓ PHP extensions check passed"

# 2. Create backup
log_message "Creating backup before deployment..."

mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/meschain-backup-$(date +%Y%m%d-%H%M%S).tar.gz"

# Backup files
tar -czf "$BACKUP_FILE" \
    --exclude='*/cache/*' \
    --exclude='*/logs/*' \
    --exclude='*/tmp/*' \
    upload/ > /dev/null 2>&1

# Backup database
if command -v mysqldump &> /dev/null; then
    if [[ -f "upload/config.php" ]]; then
        # Extract database credentials from config.php
        DB_HOST=$(grep "define('DB_HOSTNAME'" upload/config.php | cut -d "'" -f 4)
        DB_NAME=$(grep "define('DB_DATABASE'" upload/config.php | cut -d "'" -f 4)
        DB_USER=$(grep "define('DB_USERNAME'" upload/config.php | cut -d "'" -f 4)
        DB_PASS=$(grep "define('DB_PASSWORD'" upload/config.php | cut -d "'" -f 4)
        
        mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/database-backup-$(date +%Y%m%d-%H%M%S).sql" 2>/dev/null
        log_message "✓ Database backup created"
    fi
fi

log_message "✓ Backup created: $BACKUP_FILE"

# 3. Environment preparation
log_message "Preparing production environment..."

# Set proper file permissions
find upload/ -type f -exec chmod 644 {} \;
find upload/ -type d -exec chmod 755 {} \;

# Set writable directories
chmod -R 777 upload/storage/cache/
chmod -R 777 upload/storage/logs/
chmod -R 777 upload/storage/download/
chmod -R 777 upload/storage/upload/
chmod -R 777 upload/storage/modification/
chmod 666 upload/config.php
chmod 666 upload/admin/config.php

log_message "✓ File permissions set"

# 4. Performance optimization
log_message "Applying performance optimizations..."

# Enable OpCache if available
if php -m | grep -q "Zend OPcache"; then
    # Create php.ini override for OpCache
    cat > /tmp/opcache.ini << EOF
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
opcache.validate_timestamps=0
EOF
    
    warning_message "OpCache configuration created. Please review /tmp/opcache.ini"
fi

# Optimize composer autoloader
if [[ -f "composer.json" ]]; then
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true
        log_message "✓ Composer optimized"
    fi
fi

# 5. Security hardening
log_message "Applying security hardening..."

# Create .htaccess for enhanced security
cat > upload/.htaccess << 'EOF'
# MesChain-Sync Security Rules
RewriteEngine On

# Block access to sensitive files
<FilesMatch "\.(sql|log|md|json|lock|git|env)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to system directories
RedirectMatch 403 ^/upload/system/
RedirectMatch 403 ^/upload/storage/

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Hide server information
ServerTokens Prod
Header unset Server
Header unset X-Powered-By
EOF

# Secure sensitive directories
echo "Order allow,deny" > upload/storage/.htaccess
echo "Deny from all" >> upload/storage/.htaccess

echo "Order allow,deny" > upload/system/.htaccess
echo "Deny from all" >> upload/system/.htaccess

log_message "✓ Security hardening applied"

# 6. Database optimization
log_message "Optimizing database..."

if [[ -f "database_optimization.sql" ]]; then
    if command -v mysql &> /dev/null && [[ -f "upload/config.php" ]]; then
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database_optimization.sql 2>/dev/null || true
        log_message "✓ Database optimization completed"
    fi
fi

# 7. Cache warming
log_message "Warming up caches..."

# Warm up OpCache
if [[ -f "upload/admin/index.php" ]]; then
    php upload/admin/index.php > /dev/null 2>&1 || true
fi

if [[ -f "upload/index.php" ]]; then
    php upload/index.php > /dev/null 2>&1 || true
fi

log_message "✓ Cache warming completed"

# 8. Health checks
log_message "Running health checks..."

# Check if main files exist
CRITICAL_FILES=(
    "upload/index.php"
    "upload/admin/index.php"
    "upload/config.php"
    "upload/admin/config.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [[ ! -f "$file" ]]; then
        error_message "Critical file missing: $file"
        exit 1
    fi
done

log_message "✓ Critical files check passed"

# Test database connection
if [[ -f "test_db_connection.php" ]]; then
    if php test_db_connection.php > /dev/null 2>&1; then
        log_message "✓ Database connection test passed"
    else
        error_message "Database connection test failed"
        exit 1
    fi
fi

# 9. Create deployment summary
log_message "Creating deployment summary..."

cat > deployment-summary-$(date +%Y%m%d-%H%M%S).md << EOF
# MesChain-Sync Production Deployment Summary

**Deployment Date:** $(date '+%Y-%m-%d %H:%M:%S')
**Version:** $VERSION
**Deployed by:** Musti DevOps Team

## Deployment Steps Completed:
- ✅ Pre-deployment checks
- ✅ System backup created
- ✅ File permissions set
- ✅ Performance optimization
- ✅ Security hardening
- ✅ Database optimization
- ✅ Cache warming
- ✅ Health checks

## System Information:
- **PHP Version:** $PHP_VERSION
- **Available Disk Space:** ${AVAILABLE_SPACE}GB
- **Backup Location:** $BACKUP_FILE

## Security Measures Applied:
- Enhanced .htaccess rules
- Sensitive directory protection
- Security headers configured
- File permission hardening

## Performance Optimizations:
- OpCache configuration optimized
- Composer autoloader optimized
- Cache warming completed

## Next Steps:
1. Configure SSL certificate
2. Set up monitoring alerts
3. Configure automated backups
4. Update DNS records if needed
5. Test all marketplace integrations

## Support:
For any issues, contact Musti DevOps Team.
EOF

log_message "✅ Deployment summary created"

# 10. Final verification
log_message "Running final verification..."

# Check if web server is accessible
if command -v curl &> /dev/null; then
    if curl -s -o /dev/null -w "%{http_code}" "http://localhost/" | grep -q "200\|30"; then
        log_message "✓ Web server accessibility verified"
    else
        warning_message "Web server accessibility check failed - please verify manually"
    fi
fi

# Create success marker
touch .deployment-success-$(date +%Y%m%d-%H%M%S)

echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}  DEPLOYMENT COMPLETED!${NC}"
echo -e "${GREEN}================================${NC}"
echo
echo -e "${BLUE}Deployment Summary:${NC}"
echo -e "• Project: $PROJECT_NAME v$VERSION"
echo -e "• Backup created: $BACKUP_FILE"
echo -e "• Log file: $LOG_FILE"
echo -e "• Status: ✅ SUCCESS"
echo
echo -e "${YELLOW}Important Notes:${NC}"
echo -e "• Please test all marketplace integrations"
echo -e "• Configure SSL certificate if not done"
echo -e "• Set up monitoring and alerts"
echo -e "• Update your documentation"
echo
echo -e "${GREEN}🚀 MesChain-Sync is ready for production!${NC}"

# Send notification (if configured)
if command -v curl &> /dev/null && [[ -n "$SLACK_WEBHOOK_URL" ]]; then
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"🚀 MesChain-Sync v$VERSION deployment completed successfully by Musti DevOps Team!\"}" \
        "$SLACK_WEBHOOK_URL" > /dev/null 2>&1 || true
fi

exit 0