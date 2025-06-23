#!/bin/bash

# MesChain Trendyol Integration - System Health Check Script
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
LOG_FILE="${LOG_FILE:-/var/log/meschain_health.log}"
MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASSWORD="${MYSQL_PASSWORD}"
MYSQL_DATABASE="${MYSQL_DATABASE:-opencart}"

# Health check results
TOTAL_CHECKS=0
PASSED_CHECKS=0
FAILED_CHECKS=0
WARNING_CHECKS=0

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a "$LOG_FILE"
    ((FAILED_CHECKS++))
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a "$LOG_FILE"
    ((WARNING_CHECKS++))
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] SUCCESS: $1${NC}" | tee -a "$LOG_FILE"
    ((PASSED_CHECKS++))
}

check_start() {
    ((TOTAL_CHECKS++))
    info "Checking: $1"
}

# System checks
check_system_requirements() {
    log "Checking system requirements..."

    # PHP version check
    check_start "PHP Version"
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    if [[ $(echo "$PHP_VERSION >= 7.4" | bc -l) -eq 1 ]]; then
        success "PHP version $PHP_VERSION is supported"
    else
        error "PHP version $PHP_VERSION is not supported (minimum 7.4 required)"
    fi

    # PHP extensions check
    check_start "PHP Extensions"
    REQUIRED_EXTENSIONS=("curl" "json" "mysqli" "zip" "gd" "mbstring" "openssl")
    MISSING_EXTENSIONS=()

    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            MISSING_EXTENSIONS+=("$ext")
        fi
    done

    if [[ ${#MISSING_EXTENSIONS[@]} -eq 0 ]]; then
        success "All required PHP extensions are installed"
    else
        error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    fi

    # Memory limit check
    check_start "PHP Memory Limit"
    MEMORY_LIMIT=$(php -r "echo ini_get('memory_limit');")
    MEMORY_BYTES=$(php -r "echo return_bytes('$MEMORY_LIMIT');")
    if [[ $MEMORY_BYTES -ge 134217728 ]]; then  # 128MB
        success "PHP memory limit is adequate: $MEMORY_LIMIT"
    else
        warning "PHP memory limit may be too low: $MEMORY_LIMIT (recommended: 128M+)"
    fi

    # Disk space check
    check_start "Disk Space"
    AVAILABLE_SPACE=$(df "$OPENCART_ROOT" | awk 'NR==2 {print $4}')
    if [[ $AVAILABLE_SPACE -gt 1048576 ]]; then  # 1GB
        success "Sufficient disk space available: $(($AVAILABLE_SPACE / 1024))MB"
    else
        warning "Low disk space: $(($AVAILABLE_SPACE / 1024))MB available"
    fi
}

# OpenCart checks
check_opencart() {
    log "Checking OpenCart installation..."

    # OpenCart directory check
    check_start "OpenCart Directory"
    if [[ -d "$OPENCART_ROOT" ]]; then
        success "OpenCart directory exists: $OPENCART_ROOT"
    else
        error "OpenCart directory not found: $OPENCART_ROOT"
        return
    fi

    # Config file check
    check_start "OpenCart Configuration"
    if [[ -f "$OPENCART_ROOT/config.php" ]]; then
        success "OpenCart config.php found"

        # Check config syntax
        if php -l "$OPENCART_ROOT/config.php" > /dev/null 2>&1; then
            success "OpenCart config.php syntax is valid"
        else
            error "OpenCart config.php has syntax errors"
        fi
    else
        error "OpenCart config.php not found"
    fi

    # Admin config check
    check_start "Admin Configuration"
    if [[ -f "$OPENCART_ROOT/admin/config.php" ]]; then
        success "Admin config.php found"

        # Check admin config syntax
        if php -l "$OPENCART_ROOT/admin/config.php" > /dev/null 2>&1; then
            success "Admin config.php syntax is valid"
        else
            error "Admin config.php has syntax errors"
        fi
    else
        error "Admin config.php not found"
    fi

    # Storage directory check
    check_start "Storage Directories"
    STORAGE_DIRS=("storage/cache" "storage/logs" "storage/modification" "storage/session")
    for dir in "${STORAGE_DIRS[@]}"; do
        if [[ -d "$OPENCART_ROOT/$dir" && -w "$OPENCART_ROOT/$dir" ]]; then
            success "Storage directory writable: $dir"
        else
            error "Storage directory not writable: $dir"
        fi
    done
}

# Database checks
check_database() {
    log "Checking database connection..."

    # Get MySQL password if not provided
    if [[ -z "$MYSQL_PASSWORD" ]]; then
        echo -n "Enter MySQL password for user '$MYSQL_USER': "
        read -s MYSQL_PASSWORD
        echo
    fi

    # Database connection check
    check_start "Database Connection"
    if mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "USE $MYSQL_DATABASE;" 2>/dev/null; then
        success "Database connection successful"
    else
        error "Cannot connect to database: $MYSQL_DATABASE"
        return
    fi

    # MesChain tables check
    check_start "MesChain Database Tables"
    REQUIRED_TABLES=(
        "meschain_trendyol_products"
        "meschain_trendyol_orders"
        "meschain_trendyol_api_logs"
        "meschain_trendyol_webhooks"
        "meschain_trendyol_settings"
        "oc_trendyol_sync_logs"
        "oc_trendyol_webhook_logs"
    )

    MISSING_TABLES=()
    for table in "${REQUIRED_TABLES[@]}"; do
        if ! mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "DESCRIBE $table;" > /dev/null 2>&1; then
            MISSING_TABLES+=("$table")
        fi
    done

    if [[ ${#MISSING_TABLES[@]} -eq 0 ]]; then
        success "All MesChain database tables exist"
    else
        error "Missing database tables: ${MISSING_TABLES[*]}"
    fi

    # Database performance check
    check_start "Database Performance"
    QUERY_TIME=$(mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SELECT BENCHMARK(1000, MD5('test'));" 2>/dev/null | tail -n1)
    if [[ $? -eq 0 ]]; then
        success "Database performance test completed"
    else
        warning "Database performance test failed"
    fi
}

# MesChain files check
check_meschain_files() {
    log "Checking MesChain files..."

    # Core files check
    check_start "MesChain Core Files"
    CORE_FILES=(
        "admin/controller/extension/meschain/trendyol.php"
        "admin/model/extension/meschain/trendyol.php"
        "system/library/meschain/api/trendyol_client.php"
        "system/library/meschain/cron/order_sync.php"
        "system/library/meschain/cron/stock_sync.php"
        "catalog/controller/extension/meschain/webhook/trendyol.php"
    )

    MISSING_FILES=()
    for file in "${CORE_FILES[@]}"; do
        if [[ ! -f "$OPENCART_ROOT/$file" ]]; then
            MISSING_FILES+=("$file")
        fi
    done

    if [[ ${#MISSING_FILES[@]} -eq 0 ]]; then
        success "All MesChain core files exist"
    else
        error "Missing core files: ${MISSING_FILES[*]}"
    fi

    # File permissions check
    check_start "File Permissions"
    PERMISSION_ERRORS=0

    # Check admin files
    if [[ -d "$OPENCART_ROOT/admin/controller/extension/meschain" ]]; then
        if [[ ! -r "$OPENCART_ROOT/admin/controller/extension/meschain" ]]; then
            error "Admin controller directory not readable"
            ((PERMISSION_ERRORS++))
        fi
    fi

    # Check system files
    if [[ -d "$OPENCART_ROOT/system/library/meschain" ]]; then
        if [[ ! -r "$OPENCART_ROOT/system/library/meschain" ]]; then
            error "System library directory not readable"
            ((PERMISSION_ERRORS++))
        fi
    fi

    # Check storage directories
    STORAGE_DIRS=("storage/logs/meschain" "storage/cache/meschain" "storage/temp/meschain")
    for dir in "${STORAGE_DIRS[@]}"; do
        if [[ -d "$OPENCART_ROOT/$dir" ]]; then
            if [[ ! -w "$OPENCART_ROOT/$dir" ]]; then
                error "Storage directory not writable: $dir"
                ((PERMISSION_ERRORS++))
            fi
        else
            warning "Storage directory does not exist: $dir"
        fi
    done

    if [[ $PERMISSION_ERRORS -eq 0 ]]; then
        success "File permissions are correct"
    else
        error "$PERMISSION_ERRORS permission errors found"
    fi

    # PHP syntax check
    check_start "PHP Syntax"
    SYNTAX_ERRORS=0

    find "$OPENCART_ROOT/system/library/meschain" -name "*.php" -type f | while read -r file; do
        if ! php -l "$file" > /dev/null 2>&1; then
            error "PHP syntax error in: $file"
            ((SYNTAX_ERRORS++))
        fi
    done

    if [[ $SYNTAX_ERRORS -eq 0 ]]; then
        success "All PHP files have valid syntax"
    else
        error "$SYNTAX_ERRORS PHP syntax errors found"
    fi
}

# API connectivity check
check_api_connectivity() {
    log "Checking API connectivity..."

    # Trendyol API check
    check_start "Trendyol API Connectivity"
    if curl -s --connect-timeout 10 "https://api.trendyol.com" > /dev/null; then
        success "Trendyol API is reachable"
    else
        warning "Cannot reach Trendyol API (may be network or firewall issue)"
    fi

    # SSL certificate check
    check_start "SSL Certificate Validation"
    if curl -s --connect-timeout 10 "https://api.trendyol.com" > /dev/null; then
        success "SSL certificate validation successful"
    else
        warning "SSL certificate validation failed"
    fi
}

# Cron jobs check
check_cron_jobs() {
    log "Checking cron jobs..."

    check_start "Cron Jobs Configuration"
    CRON_COUNT=$(crontab -l 2>/dev/null | grep -c "meschain\|trendyol" || echo "0")

    if [[ $CRON_COUNT -gt 0 ]]; then
        success "$CRON_COUNT MesChain cron jobs configured"

        # List cron jobs
        info "Configured cron jobs:"
        crontab -l 2>/dev/null | grep "meschain\|trendyol" | while read -r line; do
            info "  $line"
        done
    else
        warning "No MesChain cron jobs found"
    fi
}

# Log files check
check_logs() {
    log "Checking log files..."

    check_start "Log Directory"
    if [[ -d "$OPENCART_ROOT/storage/logs/meschain" ]]; then
        success "MesChain log directory exists"

        # Check log file sizes
        LOG_SIZE=$(du -sh "$OPENCART_ROOT/storage/logs/meschain" 2>/dev/null | cut -f1)
        info "Log directory size: $LOG_SIZE"

        # Check for recent errors
        if [[ -f "$OPENCART_ROOT/storage/logs/meschain/error.log" ]]; then
            RECENT_ERRORS=$(tail -n 100 "$OPENCART_ROOT/storage/logs/meschain/error.log" 2>/dev/null | grep -c "$(date +%Y-%m-%d)" || echo "0")
            if [[ $RECENT_ERRORS -gt 0 ]]; then
                warning "$RECENT_ERRORS errors logged today"
            else
                success "No errors logged today"
            fi
        fi
    else
        warning "MesChain log directory not found"
    fi
}

# Performance check
check_performance() {
    log "Checking system performance..."

    # Memory usage check
    check_start "Memory Usage"
    MEMORY_USAGE=$(free | awk 'FNR==2{printf "%.2f", $3/($3+$4)*100}')
    if [[ $(echo "$MEMORY_USAGE < 80" | bc -l) -eq 1 ]]; then
        success "Memory usage is acceptable: ${MEMORY_USAGE}%"
    else
        warning "High memory usage: ${MEMORY_USAGE}%"
    fi

    # CPU load check
    check_start "CPU Load"
    CPU_LOAD=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
    if [[ $(echo "$CPU_LOAD < 2.0" | bc -l) -eq 1 ]]; then
        success "CPU load is acceptable: $CPU_LOAD"
    else
        warning "High CPU load: $CPU_LOAD"
    fi

    # Disk I/O check
    check_start "Disk I/O"
    if command -v iostat > /dev/null; then
        IO_WAIT=$(iostat -c 1 2 | tail -n1 | awk '{print $4}')
        if [[ $(echo "$IO_WAIT < 20" | bc -l) -eq 1 ]]; then
            success "Disk I/O wait is acceptable: ${IO_WAIT}%"
        else
            warning "High disk I/O wait: ${IO_WAIT}%"
        fi
    else
        info "iostat not available, skipping disk I/O check"
    fi
}

# Security check
check_security() {
    log "Checking security configuration..."

    # File permissions security
    check_start "Security Permissions"
    SECURITY_ISSUES=0

    # Check for world-writable files
    WORLD_WRITABLE=$(find "$OPENCART_ROOT/system/library/meschain" -type f -perm -002 2>/dev/null | wc -l)
    if [[ $WORLD_WRITABLE -gt 0 ]]; then
        warning "$WORLD_WRITABLE world-writable files found"
        ((SECURITY_ISSUES++))
    fi

    # Check for executable PHP files
    EXECUTABLE_PHP=$(find "$OPENCART_ROOT/system/library/meschain" -name "*.php" -perm -111 2>/dev/null | wc -l)
    if [[ $EXECUTABLE_PHP -gt 0 ]]; then
        warning "$EXECUTABLE_PHP executable PHP files found"
        ((SECURITY_ISSUES++))
    fi

    if [[ $SECURITY_ISSUES -eq 0 ]]; then
        success "No security permission issues found"
    else
        warning "$SECURITY_ISSUES security permission issues found"
    fi

    # SSL/TLS check
    check_start "SSL/TLS Configuration"
    if php -m | grep -q "openssl"; then
        success "OpenSSL extension is available"
    else
        error "OpenSSL extension is not available"
    fi
}

# Generate health report
generate_health_report() {
    log "Generating health report..."

    REPORT_FILE="$SCRIPT_DIR/health_report_$(date +%Y%m%d_%H%M%S).txt"

    cat > "$REPORT_FILE" << EOF
MesChain Trendyol Integration - Health Check Report
==================================================

Health Check Date: $(date)
System: $(uname -a)
OpenCart Root: $OPENCART_ROOT
Database: $MYSQL_DATABASE

SUMMARY
=======
Total Checks: $TOTAL_CHECKS
Passed: $PASSED_CHECKS
Failed: $FAILED_CHECKS
Warnings: $WARNING_CHECKS

Health Score: $(( (PASSED_CHECKS * 100) / TOTAL_CHECKS ))%

SYSTEM INFORMATION
==================
PHP Version: $(php -v | head -n1)
Web Server: $(apache2 -v 2>/dev/null | head -n1 || nginx -v 2>&1 | head -n1 || echo "Unknown")
MySQL Version: $(mysql --version 2>/dev/null || echo "Unknown")
Operating System: $(lsb_release -d 2>/dev/null | cut -f2 || uname -s)

RESOURCE USAGE
==============
Memory Usage: $(free | awk 'FNR==2{printf "%.2f%%", $3/($3+$4)*100}')
CPU Load: $(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
Disk Usage: $(df -h "$OPENCART_ROOT" | awk 'NR==2 {print $5}')

RECOMMENDATIONS
===============
EOF

    # Add recommendations based on results
    if [[ $FAILED_CHECKS -gt 0 ]]; then
        echo "- CRITICAL: $FAILED_CHECKS critical issues need immediate attention" >> "$REPORT_FILE"
    fi

    if [[ $WARNING_CHECKS -gt 0 ]]; then
        echo "- WARNING: $WARNING_CHECKS warnings should be reviewed" >> "$REPORT_FILE"
    fi

    if [[ $FAILED_CHECKS -eq 0 && $WARNING_CHECKS -eq 0 ]]; then
        echo "- System is healthy and operating normally" >> "$REPORT_FILE"
    fi

    cat >> "$REPORT_FILE" << EOF

NEXT STEPS
==========
1. Review any failed checks and resolve issues
2. Monitor system logs for ongoing issues
3. Run health check regularly (recommended: daily)
4. Update system components as needed

SUPPORT
=======
- Documentation: docs/TROUBLESHOOTING.md
- Logs: $OPENCART_ROOT/storage/logs/meschain/
- Health Check: Run this script regularly

Generated by: MesChain Health Check v1.0.0
EOF

    log "Health report generated: $REPORT_FILE"
}

# Main health check function
main() {
    log "Starting MesChain Trendyol Integration health check..."
    log "Version: 1.0.0"
    log "Target: $OPENCART_ROOT"

    # Initialize counters
    TOTAL_CHECKS=0
    PASSED_CHECKS=0
    FAILED_CHECKS=0
    WARNING_CHECKS=0

    # Run health checks
    check_system_requirements
    check_opencart
    check_database
    check_meschain_files
    check_api_connectivity
    check_cron_jobs
    check_logs
    check_performance
    check_security

    # Generate report
    generate_health_report

    # Display summary
    echo
    echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║                    HEALTH CHECK SUMMARY                      ║${NC}"
    echo -e "${BLUE}║                                                              ║${NC}"
    echo -e "${BLUE}║  Total Checks: $(printf "%2d" $TOTAL_CHECKS)                                          ║${NC}"
    echo -e "${GREEN}║  Passed: $(printf "%2d" $PASSED_CHECKS)                                              ║${NC}"
    echo -e "${RED}║  Failed: $(printf "%2d" $FAILED_CHECKS)                                              ║${NC}"
    echo -e "${YELLOW}║  Warnings: $(printf "%2d" $WARNING_CHECKS)                                            ║${NC}"
    echo -e "${BLUE}║                                                              ║${NC}"

    HEALTH_SCORE=$(( (PASSED_CHECKS * 100) / TOTAL_CHECKS ))
    if [[ $HEALTH_SCORE -ge 90 ]]; then
        echo -e "${GREEN}║  Health Score: ${HEALTH_SCORE}% - EXCELLENT                           ║${NC}"
    elif [[ $HEALTH_SCORE -ge 75 ]]; then
        echo -e "${YELLOW}║  Health Score: ${HEALTH_SCORE}% - GOOD                               ║${NC}"
    elif [[ $HEALTH_SCORE -ge 50 ]]; then
        echo -e "${YELLOW}║  Health Score: ${HEALTH_SCORE}% - NEEDS ATTENTION                    ║${NC}"
    else
        echo -e "${RED}║  Health Score: ${HEALTH_SCORE}% - CRITICAL                           ║${NC}"
    fi

    echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo

    # Exit with appropriate code
    if [[ $FAILED_CHECKS -gt 0 ]]; then
        log "Health check completed with failures"
        exit 1
    elif [[ $WARNING_CHECKS -gt 0 ]]; then
        log "Health check completed with warnings"
        exit 2
    else
        log "Health check completed successfully"
        exit 0
    fi
}

# Handle script interruption
trap 'error "Health check interrupted by user"' INT TERM

# Run main function
main "$@"
