#!/bin/bash

# MesChain Trendyol Integration - Production Deployment Script
# Version: 1.0.0
# Date: June 21, 2025

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
OPENCART_ROOT="${OPENCART_ROOT:-/var/www/html}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/meschain}"
LOG_FILE="${LOG_FILE:-/var/log/meschain_deploy.log}"
MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASSWORD="${MYSQL_PASSWORD}"
MYSQL_DATABASE="${MYSQL_DATABASE:-opencart}"

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}" | tee -a "$LOG_FILE"
}

# Pre-deployment checks
pre_deployment_checks() {
    log "Starting pre-deployment checks..."

    # Check if running as root or with sudo
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root or with sudo"
    fi

    # Check OpenCart directory
    if [[ ! -d "$OPENCART_ROOT" ]]; then
        error "OpenCart directory not found: $OPENCART_ROOT"
    fi

    # Check if OpenCart config exists
    if [[ ! -f "$OPENCART_ROOT/config.php" ]]; then
        error "OpenCart config.php not found in $OPENCART_ROOT"
    fi

    # Check MySQL connection
    if ! mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "USE $MYSQL_DATABASE;" 2>/dev/null; then
        error "Cannot connect to MySQL database: $MYSQL_DATABASE"
    fi

    # Check required PHP extensions
    php -m | grep -q "curl" || error "PHP curl extension is required"
    php -m | grep -q "json" || error "PHP json extension is required"
    php -m | grep -q "mysqli" || error "PHP mysqli extension is required"
    php -m | grep -q "zip" || error "PHP zip extension is required"

    # Check disk space (minimum 1GB)
    available_space=$(df "$OPENCART_ROOT" | awk 'NR==2 {print $4}')
    if [[ $available_space -lt 1048576 ]]; then
        error "Insufficient disk space. At least 1GB required."
    fi

    log "Pre-deployment checks completed successfully"
}

# Create backup
create_backup() {
    log "Creating system backup..."

    # Create backup directory
    mkdir -p "$BACKUP_DIR"

    # Backup timestamp
    BACKUP_TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/meschain_backup_$BACKUP_TIMESTAMP"

    # Create backup directories
    mkdir -p "$BACKUP_PATH/files"
    mkdir -p "$BACKUP_PATH/database"

    # Backup OpenCart files
    info "Backing up OpenCart files..."
    rsync -av --exclude='storage/cache/*' --exclude='storage/logs/*' \
          "$OPENCART_ROOT/" "$BACKUP_PATH/files/" || error "File backup failed"

    # Backup database
    info "Backing up database..."
    mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" \
              > "$BACKUP_PATH/database/opencart_backup.sql" || error "Database backup failed"

    # Create backup info file
    cat > "$BACKUP_PATH/backup_info.txt" << EOF
Backup Created: $(date)
OpenCart Root: $OPENCART_ROOT
Database: $MYSQL_DATABASE
Version: MesChain Trendyol v1.0.0
EOF

    log "Backup created successfully at: $BACKUP_PATH"
    echo "$BACKUP_PATH" > "$SCRIPT_DIR/.last_backup"
}

# Deploy OCMOD package
deploy_ocmod() {
    log "Deploying OCMOD package..."

    OCMOD_FILE="$PROJECT_ROOT/meschain_trendyol_v1.0.0.ocmod.zip"

    if [[ ! -f "$OCMOD_FILE" ]]; then
        error "OCMOD file not found: $OCMOD_FILE"
    fi

    # Extract OCMOD to temporary directory
    TEMP_DIR=$(mktemp -d)
    unzip -q "$OCMOD_FILE" -d "$TEMP_DIR" || error "Failed to extract OCMOD"

    # Copy files to OpenCart
    if [[ -d "$TEMP_DIR/upload" ]]; then
        info "Copying OCMOD files..."
        rsync -av "$TEMP_DIR/upload/" "$OPENCART_ROOT/" || error "Failed to copy OCMOD files"
    fi

    # Set proper permissions
    info "Setting file permissions..."
    chown -R www-data:www-data "$OPENCART_ROOT/admin/controller/extension/meschain/"
    chown -R www-data:www-data "$OPENCART_ROOT/admin/view/template/extension/meschain/"
    chown -R www-data:www-data "$OPENCART_ROOT/admin/language/*/extension/meschain/"
    chown -R www-data:www-data "$OPENCART_ROOT/system/library/meschain/"
    chown -R www-data:www-data "$OPENCART_ROOT/catalog/controller/extension/meschain/"

    chmod -R 644 "$OPENCART_ROOT/admin/controller/extension/meschain/"
    chmod -R 644 "$OPENCART_ROOT/admin/view/template/extension/meschain/"
    chmod -R 644 "$OPENCART_ROOT/system/library/meschain/"
    chmod -R 644 "$OPENCART_ROOT/catalog/controller/extension/meschain/"

    # Cleanup
    rm -rf "$TEMP_DIR"

    log "OCMOD package deployed successfully"
}

# Install database schema
install_database() {
    log "Installing database schema..."

    SQL_FILE="$PROJECT_ROOT/../RESTRUCTURED_UPLOAD/install/meschain_trendyol_install.sql"

    if [[ ! -f "$SQL_FILE" ]]; then
        error "SQL installation file not found: $SQL_FILE"
    fi

    # Execute SQL installation
    mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$SQL_FILE" || error "Database installation failed"

    log "Database schema installed successfully"
}

# Configure system
configure_system() {
    log "Configuring system..."

    # Create storage directories
    mkdir -p "$OPENCART_ROOT/storage/logs/meschain"
    mkdir -p "$OPENCART_ROOT/storage/cache/meschain"
    mkdir -p "$OPENCART_ROOT/storage/temp/meschain"

    # Set permissions for storage
    chown -R www-data:www-data "$OPENCART_ROOT/storage/"
    chmod -R 755 "$OPENCART_ROOT/storage/"

    # Configure log rotation
    cat > /etc/logrotate.d/meschain << EOF
$OPENCART_ROOT/storage/logs/meschain/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
EOF

    # Setup cron jobs
    info "Setting up cron jobs..."
    if [[ -f "$PROJECT_ROOT/../RESTRUCTURED_UPLOAD/scripts/setup_cron_jobs.sh" ]]; then
        bash "$PROJECT_ROOT/../RESTRUCTURED_UPLOAD/scripts/setup_cron_jobs.sh" "$OPENCART_ROOT"
    fi

    log "System configuration completed"
}

# Install monitoring
install_monitoring() {
    log "Installing monitoring system..."

    # Create monitoring directory
    mkdir -p "$OPENCART_ROOT/admin/view/template/extension/meschain/monitoring"

    # Copy monitoring files
    if [[ -f "$PROJECT_ROOT/monitoring/dashboards.json" ]]; then
        cp "$PROJECT_ROOT/monitoring/dashboards.json" "$OPENCART_ROOT/storage/meschain_dashboards.json"
    fi

    if [[ -f "$PROJECT_ROOT/monitoring/alerts.json" ]]; then
        cp "$PROJECT_ROOT/monitoring/alerts.json" "$OPENCART_ROOT/storage/meschain_alerts.json"
    fi

    # Setup monitoring cron
    (crontab -l 2>/dev/null; echo "*/5 * * * * php $OPENCART_ROOT/system/library/meschain/monitoring/health_check.php") | crontab -

    log "Monitoring system installed"
}

# Run tests
run_tests() {
    log "Running deployment tests..."

    # Basic connectivity test
    info "Testing database connectivity..."
    mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT COUNT(*) FROM meschain_trendyol_settings;" "$MYSQL_DATABASE" > /dev/null || error "Database test failed"

    # File permissions test
    info "Testing file permissions..."
    if [[ ! -w "$OPENCART_ROOT/storage/logs/meschain" ]]; then
        error "Storage directory not writable"
    fi

    # PHP syntax test
    info "Testing PHP syntax..."
    find "$OPENCART_ROOT/system/library/meschain" -name "*.php" -exec php -l {} \; | grep -q "Parse error" && error "PHP syntax errors found"

    log "Deployment tests completed successfully"
}

# Post-deployment tasks
post_deployment() {
    log "Running post-deployment tasks..."

    # Clear OpenCart cache
    info "Clearing OpenCart cache..."
    rm -rf "$OPENCART_ROOT/storage/cache/*"
    rm -rf "$OPENCART_ROOT/storage/modification/*"

    # Refresh modifications
    info "Refreshing modifications..."
    php "$OPENCART_ROOT/admin/cli_commands.php" refresh

    # Restart web server
    info "Restarting web server..."
    systemctl restart apache2 2>/dev/null || systemctl restart nginx 2>/dev/null || warning "Could not restart web server"

    # Restart PHP-FPM if available
    systemctl restart php7.4-fpm 2>/dev/null || systemctl restart php8.0-fpm 2>/dev/null || true

    log "Post-deployment tasks completed"
}

# Generate deployment report
generate_report() {
    log "Generating deployment report..."

    REPORT_FILE="$SCRIPT_DIR/deployment_report_$(date +%Y%m%d_%H%M%S).txt"

    cat > "$REPORT_FILE" << EOF
MesChain Trendyol Integration - Deployment Report
================================================

Deployment Date: $(date)
Deployment Status: SUCCESS
Version: 1.0.0

System Information:
- OpenCart Root: $OPENCART_ROOT
- Database: $MYSQL_DATABASE
- PHP Version: $(php -v | head -n1)
- Web Server: $(apache2 -v 2>/dev/null | head -n1 || nginx -v 2>&1 | head -n1)

Backup Information:
- Backup Location: $(cat "$SCRIPT_DIR/.last_backup" 2>/dev/null || echo "N/A")

Files Deployed:
- Admin Controllers: ✓
- Admin Views: ✓
- System Libraries: ✓
- Catalog Controllers: ✓
- Language Files: ✓
- Database Schema: ✓

Configuration:
- Storage Directories: ✓
- File Permissions: ✓
- Cron Jobs: ✓
- Log Rotation: ✓
- Monitoring: ✓

Next Steps:
1. Access admin panel: $OPENCART_ROOT/admin
2. Navigate to Extensions > MesChain > Trendyol
3. Configure API credentials
4. Run initial synchronization
5. Monitor system logs

Support:
- Documentation: See docs/ directory
- Logs: $OPENCART_ROOT/storage/logs/meschain/
- Health Check: ./health_check.sh
EOF

    log "Deployment report generated: $REPORT_FILE"
}

# Main deployment function
main() {
    log "Starting MesChain Trendyol Integration deployment..."
    log "Version: 1.0.0"
    log "Target: $OPENCART_ROOT"

    # Check if MySQL password is provided
    if [[ -z "$MYSQL_PASSWORD" ]]; then
        echo -n "Enter MySQL password for user '$MYSQL_USER': "
        read -s MYSQL_PASSWORD
        echo
    fi

    # Run deployment steps
    pre_deployment_checks
    create_backup
    deploy_ocmod
    install_database
    configure_system
    install_monitoring
    run_tests
    post_deployment
    generate_report

    log "Deployment completed successfully!"
    log "Please check the deployment report for next steps."

    # Display success message
    echo
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                    DEPLOYMENT SUCCESSFUL                     ║${NC}"
    echo -e "${GREEN}║                                                              ║${NC}"
    echo -e "${GREEN}║  MesChain Trendyol Integration v1.0.0 has been deployed     ║${NC}"
    echo -e "${GREEN}║  successfully to your OpenCart installation.                ║${NC}"
    echo -e "${GREEN}║                                                              ║${NC}"
    echo -e "${GREEN}║  Next Steps:                                                 ║${NC}"
    echo -e "${GREEN}║  1. Access your admin panel                                  ║${NC}"
    echo -e "${GREEN}║  2. Go to Extensions > MesChain > Trendyol                   ║${NC}"
    echo -e "${GREEN}║  3. Configure your API credentials                           ║${NC}"
    echo -e "${GREEN}║  4. Run initial synchronization                              ║${NC}"
    echo -e "${GREEN}║                                                              ║${NC}"
    echo -e "${GREEN}║  Support: docs/PRODUCTION_GUIDE.md                          ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo
}

# Handle script interruption
trap 'error "Deployment interrupted by user"' INT TERM

# Run main function
main "$@"
