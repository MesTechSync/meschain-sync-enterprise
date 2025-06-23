#!/bin/bash

# MesChain Trendyol Cron Jobs Setup Script
# Day 5-6: Automated cron job installation
# Version: 1.0.0
# Compatible with: OpenCart 4.0.2.3

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OPENCART_ROOT="$(dirname "$SCRIPT_DIR")"
PHP_PATH="/usr/bin/php"
CRON_USER="www-data"
LOG_DIR="/var/log/trendyol-cron"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root or with sudo privileges"
        exit 1
    fi
}

# Function to detect PHP path
detect_php() {
    print_status "Detecting PHP installation..."

    local php_paths=("/usr/bin/php" "/usr/local/bin/php" "/opt/php/bin/php" "/usr/bin/php8.1" "/usr/bin/php8.0" "/usr/bin/php7.4")

    for path in "${php_paths[@]}"; do
        if [[ -x "$path" ]]; then
            PHP_PATH="$path"
            print_success "Found PHP at: $PHP_PATH"

            # Check PHP version
            local php_version=$($PHP_PATH -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
            print_status "PHP Version: $php_version"

            if [[ $(echo "$php_version >= 7.4" | bc -l) -eq 1 ]]; then
                print_success "PHP version is compatible"
                return 0
            else
                print_warning "PHP version $php_version may not be fully compatible (recommended: 7.4+)"
            fi
            break
        fi
    done

    if [[ ! -x "$PHP_PATH" ]]; then
        print_error "PHP not found in common locations. Please install PHP CLI or specify the correct path."
        exit 1
    fi
}

# Function to verify OpenCart installation
verify_opencart() {
    print_status "Verifying OpenCart installation..."

    if [[ ! -f "$OPENCART_ROOT/config.php" ]]; then
        print_error "OpenCart config.php not found at: $OPENCART_ROOT/config.php"
        print_error "Please run this script from the OpenCart root directory or adjust OPENCART_ROOT"
        exit 1
    fi

    if [[ ! -d "$OPENCART_ROOT/system/library/meschain/cron" ]]; then
        print_error "MesChain Trendyol cron scripts not found"
        print_error "Please ensure the extension is properly installed"
        exit 1
    fi

    print_success "OpenCart installation verified"
}

# Function to create log directory
create_log_dir() {
    print_status "Creating log directory..."

    if [[ ! -d "$LOG_DIR" ]]; then
        mkdir -p "$LOG_DIR"
        chown "$CRON_USER:$CRON_USER" "$LOG_DIR"
        chmod 755 "$LOG_DIR"
        print_success "Log directory created: $LOG_DIR"
    else
        print_status "Log directory already exists: $LOG_DIR"
    fi
}

# Function to test cron scripts
test_cron_scripts() {
    print_status "Testing cron scripts..."

    local scripts=(
        "trendyol_sync.php"
        "product_sync.php"
        "order_sync.php"
        "stock_sync.php"
        "webhook_processor.php"
    )

    for script in "${scripts[@]}"; do
        local script_path="$OPENCART_ROOT/system/library/meschain/cron/$script"

        if [[ ! -f "$script_path" ]]; then
            print_error "Script not found: $script_path"
            exit 1
        fi

        if [[ ! -x "$script_path" ]]; then
            chmod +x "$script_path"
            print_status "Made executable: $script"
        fi

        # Test syntax
        if $PHP_PATH -l "$script_path" > /dev/null 2>&1; then
            print_success "Syntax OK: $script"
        else
            print_error "Syntax error in: $script"
            $PHP_PATH -l "$script_path"
            exit 1
        fi
    done
}

# Function to backup existing crontab
backup_crontab() {
    print_status "Backing up existing crontab..."

    local backup_file="/tmp/crontab_backup_$(date +%Y%m%d_%H%M%S).txt"

    if crontab -u "$CRON_USER" -l > "$backup_file" 2>/dev/null; then
        print_success "Crontab backed up to: $backup_file"
    else
        print_status "No existing crontab found for user: $CRON_USER"
    fi
}

# Function to install cron jobs
install_cron_jobs() {
    print_status "Installing Trendyol cron jobs..."

    # Get current crontab
    local temp_cron="/tmp/trendyol_cron_$(date +%s).txt"
    crontab -u "$CRON_USER" -l > "$temp_cron" 2>/dev/null || touch "$temp_cron"

    # Remove existing Trendyol cron jobs
    sed -i '/# MesChain Trendyol/d' "$temp_cron"
    sed -i '/meschain\/cron\/.*\.php/d' "$temp_cron"

    # Add new cron jobs
    cat >> "$temp_cron" << EOF

# MesChain Trendyol Cron Jobs - Auto-generated on $(date)
# Main synchronization (every 15 minutes)
*/15 * * * * $PHP_PATH $OPENCART_ROOT/system/library/meschain/cron/trendyol_sync.php >> $LOG_DIR/trendyol_sync.log 2>&1

# Product synchronization (every hour)
0 * * * * $PHP_PATH $OPENCART_ROOT/system/library/meschain/cron/product_sync.php >> $LOG_DIR/product_sync.log 2>&1

# Order synchronization (every 10 minutes)
*/10 * * * * $PHP_PATH $OPENCART_ROOT/system/library/meschain/cron/order_sync.php >> $LOG_DIR/order_sync.log 2>&1

# Stock synchronization (every 30 minutes)
*/30 * * * * $PHP_PATH $OPENCART_ROOT/system/library/meschain/cron/stock_sync.php >> $LOG_DIR/stock_sync.log 2>&1

# Webhook processing (every 5 minutes)
*/5 * * * * $PHP_PATH $OPENCART_ROOT/system/library/meschain/cron/webhook_processor.php >> $LOG_DIR/webhook_processor.log 2>&1

EOF

    # Install the new crontab
    if crontab -u "$CRON_USER" "$temp_cron"; then
        print_success "Cron jobs installed successfully"
    else
        print_error "Failed to install cron jobs"
        exit 1
    fi

    # Clean up
    rm -f "$temp_cron"
}

# Function to setup log rotation
setup_log_rotation() {
    print_status "Setting up log rotation..."

    local logrotate_config="/etc/logrotate.d/trendyol-cron"

    cat > "$logrotate_config" << EOF
$LOG_DIR/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 $CRON_USER $CRON_USER
    postrotate
        # Restart cron if needed
        /bin/systemctl reload cron > /dev/null 2>&1 || true
    endscript
}
EOF

    print_success "Log rotation configured"
}

# Function to create monitoring script
create_monitoring_script() {
    print_status "Creating monitoring script..."

    local monitor_script="/usr/local/bin/trendyol-cron-monitor"

    cat > "$monitor_script" << 'EOF'
#!/bin/bash

# MesChain Trendyol Cron Monitor
# Check if cron jobs are running properly

LOG_DIR="/var/log/trendyol-cron"
ALERT_EMAIL=""

check_log_age() {
    local log_file="$1"
    local max_age_minutes="$2"

    if [[ ! -f "$log_file" ]]; then
        echo "WARNING: Log file not found: $log_file"
        return 1
    fi

    local file_age=$(( ($(date +%s) - $(stat -c %Y "$log_file")) / 60 ))

    if [[ $file_age -gt $max_age_minutes ]]; then
        echo "WARNING: $log_file is $file_age minutes old (max: $max_age_minutes)"
        return 1
    fi

    return 0
}

# Check log files
echo "Checking Trendyol cron job logs..."

check_log_age "$LOG_DIR/trendyol_sync.log" 20
check_log_age "$LOG_DIR/product_sync.log" 70
check_log_age "$LOG_DIR/order_sync.log" 15
check_log_age "$LOG_DIR/stock_sync.log" 35
check_log_age "$LOG_DIR/webhook_processor.log" 10

# Check for errors in recent logs
echo "Checking for recent errors..."

for log_file in "$LOG_DIR"/*.log; do
    if [[ -f "$log_file" ]]; then
        local recent_errors=$(tail -n 100 "$log_file" | grep -i "error\|fatal\|exception" | wc -l)
        if [[ $recent_errors -gt 0 ]]; then
            echo "WARNING: Found $recent_errors recent errors in $(basename "$log_file")"
        fi
    fi
done

echo "Monitoring check completed at $(date)"
EOF

    chmod +x "$monitor_script"
    print_success "Monitoring script created: $monitor_script"

    # Add monitoring to crontab (run every hour)
    local temp_cron="/tmp/monitor_cron_$(date +%s).txt"
    crontab -u "$CRON_USER" -l > "$temp_cron" 2>/dev/null || touch "$temp_cron"

    # Remove existing monitor job
    sed -i '/trendyol-cron-monitor/d' "$temp_cron"

    # Add monitor job
    echo "0 * * * * /usr/local/bin/trendyol-cron-monitor >> $LOG_DIR/monitor.log 2>&1" >> "$temp_cron"

    crontab -u "$CRON_USER" "$temp_cron"
    rm -f "$temp_cron"

    print_success "Monitoring cron job added"
}

# Function to display final status
display_status() {
    print_success "=== Trendyol Cron Jobs Setup Complete ==="
    echo
    print_status "Installed cron jobs:"
    crontab -u "$CRON_USER" -l | grep -A 20 "MesChain Trendyol"
    echo
    print_status "Log directory: $LOG_DIR"
    print_status "Monitor script: /usr/local/bin/trendyol-cron-monitor"
    echo
    print_status "To verify installation:"
    echo "  sudo crontab -u $CRON_USER -l"
    echo "  sudo tail -f $LOG_DIR/trendyol_sync.log"
    echo
    print_status "To monitor cron jobs:"
    echo "  sudo /usr/local/bin/trendyol-cron-monitor"
    echo
    print_warning "Important: Make sure to configure your Trendyol API credentials in OpenCart admin panel"
}

# Main execution
main() {
    echo "MesChain Trendyol Cron Jobs Setup Script"
    echo "========================================"
    echo

    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --php-path)
                PHP_PATH="$2"
                shift 2
                ;;
            --cron-user)
                CRON_USER="$2"
                shift 2
                ;;
            --log-dir)
                LOG_DIR="$2"
                shift 2
                ;;
            --help)
                echo "Usage: $0 [options]"
                echo "Options:"
                echo "  --php-path PATH    Path to PHP binary (default: /usr/bin/php)"
                echo "  --cron-user USER   User to run cron jobs (default: www-data)"
                echo "  --log-dir DIR      Directory for log files (default: /var/log/trendyol-cron)"
                echo "  --help             Show this help message"
                exit 0
                ;;
            *)
                print_error "Unknown option: $1"
                exit 1
                ;;
        esac
    done

    # Run setup steps
    check_root
    detect_php
    verify_opencart
    create_log_dir
    test_cron_scripts
    backup_crontab
    install_cron_jobs
    setup_log_rotation
    create_monitoring_script
    display_status

    print_success "Setup completed successfully!"
}

# Run main function
main "$@"
