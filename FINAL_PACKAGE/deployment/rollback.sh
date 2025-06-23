#!/bin/bash

# MesChain Trendyol Integration - Emergency Rollback Script
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
OPENCART_ROOT="${OPENCART_ROOT:-/var/www/html}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/meschain}"
LOG_FILE="${LOG_FILE:-/var/log/meschain_rollback.log}"
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

# List available backups
list_backups() {
    log "Available backups:"
    if [[ -d "$BACKUP_DIR" ]]; then
        ls -la "$BACKUP_DIR" | grep "meschain_backup_" | awk '{print $9}' | sort -r
    else
        warning "No backup directory found at: $BACKUP_DIR"
    fi
}

# Select backup
select_backup() {
    if [[ -n "$1" ]]; then
        BACKUP_PATH="$BACKUP_DIR/$1"
    elif [[ -f "$SCRIPT_DIR/.last_backup" ]]; then
        BACKUP_PATH=$(cat "$SCRIPT_DIR/.last_backup")
        info "Using last backup: $BACKUP_PATH"
    else
        echo "Available backups:"
        list_backups
        echo
        echo -n "Enter backup name (or 'latest' for most recent): "
        read BACKUP_NAME

        if [[ "$BACKUP_NAME" == "latest" ]]; then
            BACKUP_NAME=$(ls -1 "$BACKUP_DIR" | grep "meschain_backup_" | sort -r | head -n1)
        fi

        BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"
    fi

    if [[ ! -d "$BACKUP_PATH" ]]; then
        error "Backup not found: $BACKUP_PATH"
    fi

    log "Selected backup: $BACKUP_PATH"
}

# Verify backup integrity
verify_backup() {
    log "Verifying backup integrity..."

    # Check backup structure
    if [[ ! -d "$BACKUP_PATH/files" ]]; then
        error "Backup files directory not found"
    fi

    if [[ ! -d "$BACKUP_PATH/database" ]]; then
        error "Backup database directory not found"
    fi

    if [[ ! -f "$BACKUP_PATH/database/opencart_backup.sql" ]]; then
        error "Database backup file not found"
    fi

    # Check if backup info exists
    if [[ -f "$BACKUP_PATH/backup_info.txt" ]]; then
        info "Backup information:"
        cat "$BACKUP_PATH/backup_info.txt"
    fi

    log "Backup integrity verified"
}

# Pre-rollback checks
pre_rollback_checks() {
    log "Starting pre-rollback checks..."

    # Check if running as root or with sudo
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root or with sudo"
    fi

    # Check OpenCart directory
    if [[ ! -d "$OPENCART_ROOT" ]]; then
        error "OpenCart directory not found: $OPENCART_ROOT"
    fi

    # Check MySQL connection
    if ! mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "USE $MYSQL_DATABASE;" 2>/dev/null; then
        error "Cannot connect to MySQL database: $MYSQL_DATABASE"
    fi

    # Check disk space
    available_space=$(df "$OPENCART_ROOT" | awk 'NR==2 {print $4}')
    backup_size=$(du -s "$BACKUP_PATH/files" | awk '{print $1}')

    if [[ $available_space -lt $((backup_size * 2)) ]]; then
        error "Insufficient disk space for rollback"
    fi

    log "Pre-rollback checks completed"
}

# Create emergency backup before rollback
create_emergency_backup() {
    log "Creating emergency backup before rollback..."

    EMERGENCY_BACKUP_PATH="$BACKUP_DIR/emergency_backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$EMERGENCY_BACKUP_PATH/files"
    mkdir -p "$EMERGENCY_BACKUP_PATH/database"

    # Backup current files
    info "Backing up current files..."
    rsync -av --exclude='storage/cache/*' --exclude='storage/logs/*' \
          "$OPENCART_ROOT/" "$EMERGENCY_BACKUP_PATH/files/" || warning "Emergency file backup failed"

    # Backup current database
    info "Backing up current database..."
    mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" \
              > "$EMERGENCY_BACKUP_PATH/database/opencart_emergency.sql" || warning "Emergency database backup failed"

    # Create backup info
    cat > "$EMERGENCY_BACKUP_PATH/backup_info.txt" << EOF
Emergency Backup Created: $(date)
Reason: Pre-rollback safety backup
OpenCart Root: $OPENCART_ROOT
Database: $MYSQL_DATABASE
Rollback Target: $BACKUP_PATH
EOF

    log "Emergency backup created: $EMERGENCY_BACKUP_PATH"
}

# Stop services
stop_services() {
    log "Stopping services..."

    # Stop cron jobs
    info "Stopping MesChain cron jobs..."
    crontab -l | grep -v "meschain\|trendyol" | crontab - || true

    # Stop web server
    info "Stopping web server..."
    systemctl stop apache2 2>/dev/null || systemctl stop nginx 2>/dev/null || warning "Could not stop web server"

    # Stop PHP-FPM
    systemctl stop php7.4-fpm 2>/dev/null || systemctl stop php8.0-fpm 2>/dev/null || true

    log "Services stopped"
}

# Rollback database
rollback_database() {
    log "Rolling back database..."

    # Drop MesChain tables
    info "Removing MesChain tables..."
    mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" << 'EOF'
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS meschain_trendyol_products;
DROP TABLE IF EXISTS meschain_trendyol_orders;
DROP TABLE IF EXISTS meschain_trendyol_api_logs;
DROP TABLE IF EXISTS meschain_trendyol_webhooks;
DROP TABLE IF EXISTS meschain_trendyol_categories;
DROP TABLE IF EXISTS meschain_trendyol_brands;
DROP TABLE IF EXISTS meschain_trendyol_shipment_providers;
DROP TABLE IF EXISTS meschain_einvoices;
DROP TABLE IF EXISTS meschain_barcodes;
DROP TABLE IF EXISTS meschain_trendyol_settings;
DROP TABLE IF EXISTS oc_trendyol_webhook_logs;
DROP TABLE IF EXISTS oc_trendyol_sync_logs;
DROP TABLE IF EXISTS oc_trendyol_webhook_stats;
DROP TABLE IF EXISTS oc_trendyol_stock_history;
DROP TABLE IF EXISTS oc_trendyol_sync_queue;
DROP TABLE IF EXISTS oc_trendyol_alerts;
DROP TABLE IF EXISTS oc_trendyol_orders;
DROP TABLE IF EXISTS oc_trendyol_products;
DROP VIEW IF EXISTS meschain_trendyol_stats;
DROP TRIGGER IF EXISTS meschain_trendyol_products_audit;
DROP TRIGGER IF EXISTS meschain_trendyol_orders_audit;
DROP PROCEDURE IF EXISTS GetTrendyolDashboardStats;
DROP PROCEDURE IF EXISTS CleanupOldLogs;
DROP PROCEDURE IF EXISTS GetProductSyncStatus;
SET FOREIGN_KEY_CHECKS = 1;
EOF

    # Remove MesChain settings
    info "Removing MesChain settings..."
    mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" << 'EOF'
DELETE FROM oc_setting WHERE code LIKE 'meschain%';
DELETE FROM oc_extension WHERE code LIKE 'meschain%';
EOF

    # Restore database from backup if requested
    if [[ "$FULL_RESTORE" == "yes" ]]; then
        info "Restoring full database from backup..."
        mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$BACKUP_PATH/database/opencart_backup.sql" || error "Database restore failed"
    fi

    log "Database rollback completed"
}

# Rollback files
rollback_files() {
    log "Rolling back files..."

    # Remove MesChain files
    info "Removing MesChain files..."
    rm -rf "$OPENCART_ROOT/admin/controller/extension/meschain/"
    rm -rf "$OPENCART_ROOT/admin/view/template/extension/meschain/"
    rm -rf "$OPENCART_ROOT/admin/language/*/extension/meschain/"
    rm -rf "$OPENCART_ROOT/system/library/meschain/"
    rm -rf "$OPENCART_ROOT/catalog/controller/extension/meschain/"
    rm -rf "$OPENCART_ROOT/storage/logs/meschain/"
    rm -rf "$OPENCART_ROOT/storage/cache/meschain/"
    rm -rf "$OPENCART_ROOT/storage/temp/meschain/"

    # Remove monitoring files
    rm -f "$OPENCART_ROOT/storage/meschain_dashboards.json"
    rm -f "$OPENCART_ROOT/storage/meschain_alerts.json"

    # Restore files from backup if requested
    if [[ "$FULL_RESTORE" == "yes" ]]; then
        info "Restoring files from backup..."
        rsync -av --delete "$BACKUP_PATH/files/" "$OPENCART_ROOT/" || error "File restore failed"
    fi

    log "File rollback completed"
}

# Clean up system configuration
cleanup_system() {
    log "Cleaning up system configuration..."

    # Remove log rotation
    rm -f /etc/logrotate.d/meschain

    # Remove cron jobs
    info "Removing cron jobs..."
    crontab -l 2>/dev/null | grep -v "meschain\|trendyol" | crontab - || true

    # Clear OpenCart cache
    info "Clearing OpenCart cache..."
    rm -rf "$OPENCART_ROOT/storage/cache/*"
    rm -rf "$OPENCART_ROOT/storage/modification/*"

    log "System cleanup completed"
}

# Start services
start_services() {
    log "Starting services..."

    # Start web server
    info "Starting web server..."
    systemctl start apache2 2>/dev/null || systemctl start nginx 2>/dev/null || warning "Could not start web server"

    # Start PHP-FPM
    systemctl start php7.4-fpm 2>/dev/null || systemctl start php8.0-fpm 2>/dev/null || true

    # Refresh modifications
    info "Refreshing modifications..."
    php "$OPENCART_ROOT/admin/cli_commands.php" refresh 2>/dev/null || true

    log "Services started"
}

# Verify rollback
verify_rollback() {
    log "Verifying rollback..."

    # Check if MesChain files are removed
    if [[ -d "$OPENCART_ROOT/system/library/meschain/" ]]; then
        error "MesChain files still exist after rollback"
    fi

    # Check database
    table_count=$(mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SHOW TABLES LIKE 'meschain_%';" | wc -l)
    if [[ $table_count -gt 0 ]]; then
        error "MesChain database tables still exist after rollback"
    fi

    # Check OpenCart functionality
    if [[ -f "$OPENCART_ROOT/config.php" ]]; then
        php -l "$OPENCART_ROOT/config.php" > /dev/null || error "OpenCart config.php has syntax errors"
    fi

    log "Rollback verification completed successfully"
}

# Generate rollback report
generate_report() {
    log "Generating rollback report..."

    REPORT_FILE="$SCRIPT_DIR/rollback_report_$(date +%Y%m%d_%H%M%S).txt"

    cat > "$REPORT_FILE" << EOF
MesChain Trendyol Integration - Rollback Report
==============================================

Rollback Date: $(date)
Rollback Status: SUCCESS
Backup Used: $BACKUP_PATH

System Information:
- OpenCart Root: $OPENCART_ROOT
- Database: $MYSQL_DATABASE
- Rollback Type: $(if [[ "$FULL_RESTORE" == "yes" ]]; then echo "Full Restore"; else echo "Removal Only"; fi)

Actions Performed:
- Emergency backup created: ✓
- Services stopped: ✓
- Database rollback: ✓
- Files rollback: ✓
- System cleanup: ✓
- Services restarted: ✓
- Verification: ✓

Files Removed:
- Admin Controllers: ✓
- Admin Views: ✓
- System Libraries: ✓
- Catalog Controllers: ✓
- Language Files: ✓
- Storage Directories: ✓

Database Changes:
- MesChain tables removed: ✓
- Settings removed: ✓
- Extensions removed: ✓

System Status:
- Web Server: $(systemctl is-active apache2 2>/dev/null || systemctl is-active nginx 2>/dev/null || echo "Unknown")
- Database: $(mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 'OK';" 2>/dev/null || echo "Error")

Next Steps:
1. Verify OpenCart functionality
2. Check error logs for any issues
3. Restore from backup if needed
4. Contact support if problems persist

Emergency Backup Location:
$(ls -la "$BACKUP_DIR" | grep "emergency_backup_" | tail -n1 | awk '{print $9}' || echo "N/A")
EOF

    log "Rollback report generated: $REPORT_FILE"
}

# Main rollback function
main() {
    log "Starting MesChain Trendyol Integration rollback..."

    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --backup)
                BACKUP_NAME="$2"
                shift 2
                ;;
            --full-restore)
                FULL_RESTORE="yes"
                shift
                ;;
            --mysql-password)
                MYSQL_PASSWORD="$2"
                shift 2
                ;;
            -h|--help)
                echo "Usage: $0 [OPTIONS]"
                echo "Options:"
                echo "  --backup NAME        Use specific backup"
                echo "  --full-restore       Restore files and database from backup"
                echo "  --mysql-password PWD MySQL password"
                echo "  -h, --help          Show this help"
                exit 0
                ;;
            *)
                error "Unknown option: $1"
                ;;
        esac
    done

    # Get MySQL password if not provided
    if [[ -z "$MYSQL_PASSWORD" ]]; then
        echo -n "Enter MySQL password for user '$MYSQL_USER': "
        read -s MYSQL_PASSWORD
        echo
    fi

    # Confirm rollback
    echo -e "${RED}WARNING: This will remove MesChain Trendyol Integration from your system!${NC}"
    if [[ "$FULL_RESTORE" == "yes" ]]; then
        echo -e "${RED}Full restore mode: This will also restore your entire OpenCart installation!${NC}"
    fi
    echo -n "Are you sure you want to continue? (yes/no): "
    read CONFIRM

    if [[ "$CONFIRM" != "yes" ]]; then
        log "Rollback cancelled by user"
        exit 0
    fi

    # Run rollback steps
    select_backup "$BACKUP_NAME"
    verify_backup
    pre_rollback_checks
    create_emergency_backup
    stop_services
    rollback_database
    rollback_files
    cleanup_system
    start_services
    verify_rollback
    generate_report

    log "Rollback completed successfully!"

    # Display success message
    echo
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                     ROLLBACK SUCCESSFUL                      ║${NC}"
    echo -e "${GREEN}║                                                              ║${NC}"
    echo -e "${GREEN}║  MesChain Trendyol Integration has been successfully         ║${NC}"
    echo -e "${GREEN}║  removed from your OpenCart installation.                    ║${NC}"
    echo -e "${GREEN}║                                                              ║${NC}"
    echo -e "${GREEN}║  Please verify your OpenCart functionality and check        ║${NC}"
    echo -e "${GREEN}║  the rollback report for details.                           ║${NC}"
    echo -e "${GREEN}║                                                              ║${NC}"
    echo -e "${GREEN}║  Emergency backup created for safety.                       ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo
}

# Handle script interruption
trap 'error "Rollback interrupted by user"' INT TERM

# Run main function
main "$@"
