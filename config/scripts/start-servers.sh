#!/bin/bash

# OpenCart Multi-Port Server Startup Script
# This script starts both OpenCart instances on ports 8080 and 8090

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_DIR="$(dirname "$SCRIPT_DIR")"
PROJECT_ROOT="$(dirname "$CONFIG_DIR")"

# Logging
LOG_DIR="/var/log/opencart-multiport"
mkdir -p "$LOG_DIR"
LOGFILE="$LOG_DIR/startup-$(date +%Y%m%d-%H%M%S).log"

# Function to log messages
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOGFILE"
}

log_success() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] SUCCESS:${NC} $1" | tee -a "$LOGFILE"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOGFILE"
}

log_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOGFILE"
}

# Function to check if port is available
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        return 1
    else
        return 0
    fi
}

# Function to create necessary directories
create_directories() {
    log "Creating necessary directories..."

    # Web directories
    sudo mkdir -p /var/www/html/opencart-8080
    sudo mkdir -p /var/www/html/opencart-8090

    # Log directories
    sudo mkdir -p /var/log/php
    sudo mkdir -p /var/log/nginx
    sudo mkdir -p /var/log/apache2

    # Session directories
    sudo mkdir -p /var/lib/php/sessions/opencart-8080
    sudo mkdir -p /var/lib/php/sessions/opencart-8090

    # Set permissions
    sudo chown -R www-data:www-data /var/www/html/opencart-8080
    sudo chown -R www-data:www-data /var/www/html/opencart-8090
    sudo chown -R www-data:www-data /var/lib/php/sessions/opencart-8080
    sudo chown -R www-data:www-data /var/lib/php/sessions/opencart-8090

    log_success "Directories created successfully"
}

# Function to setup PHP-FPM pools
setup_php_fpm() {
    log "Setting up PHP-FPM pools..."

    # Copy pool configurations
    sudo cp "$CONFIG_DIR/php/php-fpm-8080.conf" /etc/php/8.1/fpm/pool.d/
    sudo cp "$CONFIG_DIR/php/php-fpm-8090.conf" /etc/php/8.1/fpm/pool.d/

    # Test PHP-FPM configuration
    if sudo php-fpm8.1 -t; then
        log_success "PHP-FPM configuration is valid"
    else
        log_error "PHP-FPM configuration test failed"
        exit 1
    fi

    # Restart PHP-FPM
    sudo systemctl restart php8.1-fpm
    if sudo systemctl is-active --quiet php8.1-fpm; then
        log_success "PHP-FPM restarted successfully"
    else
        log_error "Failed to restart PHP-FPM"
        exit 1
    fi
}

# Function to setup web server (Apache or Nginx)
setup_webserver() {
    log "Setting up web server..."

    # Check if Apache is installed
    if command -v apache2 >/dev/null 2>&1; then
        log "Configuring Apache..."

        # Enable required modules
        sudo a2enmod rewrite
        sudo a2enmod headers
        sudo a2enmod ssl
        sudo a2enmod proxy_fcgi

        # Copy virtual host configurations
        sudo cp "$CONFIG_DIR/apache/opencart-8080.conf" /etc/apache2/sites-available/
        sudo cp "$CONFIG_DIR/apache/opencart-8090.conf" /etc/apache2/sites-available/

        # Enable sites
        sudo a2ensite opencart-8080.conf
        sudo a2ensite opencart-8090.conf

        # Test Apache configuration
        if sudo apache2ctl configtest; then
            log_success "Apache configuration is valid"
        else
            log_error "Apache configuration test failed"
            exit 1
        fi

        # Restart Apache
        sudo systemctl restart apache2
        if sudo systemctl is-active --quiet apache2; then
            log_success "Apache restarted successfully"
        else
            log_error "Failed to restart Apache"
            exit 1
        fi

    # Check if Nginx is installed
    elif command -v nginx >/dev/null 2>&1; then
        log "Configuring Nginx..."

        # Copy server configurations
        sudo cp "$CONFIG_DIR/nginx/opencart-8080.conf" /etc/nginx/sites-available/
        sudo cp "$CONFIG_DIR/nginx/opencart-8090.conf" /etc/nginx/sites-available/

        # Enable sites
        sudo ln -sf /etc/nginx/sites-available/opencart-8080.conf /etc/nginx/sites-enabled/
        sudo ln -sf /etc/nginx/sites-available/opencart-8090.conf /etc/nginx/sites-enabled/

        # Test Nginx configuration
        if sudo nginx -t; then
            log_success "Nginx configuration is valid"
        else
            log_error "Nginx configuration test failed"
            exit 1
        fi

        # Restart Nginx
        sudo systemctl restart nginx
        if sudo systemctl is-active --quiet nginx; then
            log_success "Nginx restarted successfully"
        else
            log_error "Failed to restart Nginx"
            exit 1
        fi
    else
        log_error "Neither Apache nor Nginx found. Please install a web server."
        exit 1
    fi
}

# Function to check services
check_services() {
    log "Checking services status..."

    # Check ports
    if check_port 8080; then
        log_error "Port 8080 is not responding"
    else
        log_success "Port 8080 is active"
    fi

    if check_port 8090; then
        log_error "Port 8090 is not responding"
    else
        log_success "Port 8090 is active"
    fi

    # Check PHP-FPM sockets
    if [ -S /var/run/php/php8.1-fpm-8080.sock ]; then
        log_success "PHP-FPM socket for port 8080 is active"
    else
        log_error "PHP-FPM socket for port 8080 not found"
    fi

    if [ -S /var/run/php/php8.1-fpm-8090.sock ]; then
        log_success "PHP-FPM socket for port 8090 is active"
    else
        log_error "PHP-FPM socket for port 8090 not found"
    fi
}

# Main execution
main() {
    log "Starting OpenCart Multi-Port Server Setup..."
    log "Configuration directory: $CONFIG_DIR"
    log "Project root: $PROJECT_ROOT"
    log "Log file: $LOGFILE"

    # Check if running as root or with sudo
    if [[ $EUID -ne 0 ]] && ! sudo -n true 2>/dev/null; then
        log_error "This script requires sudo privileges. Please run with sudo or as root."
        exit 1
    fi

    # Create directories
    create_directories

    # Setup PHP-FPM
    setup_php_fpm

    # Setup web server
    setup_webserver

    # Wait a moment for services to start
    sleep 3

    # Check services
    check_services

    log_success "OpenCart Multi-Port Server setup completed!"
    log "Access your OpenCart instances at:"
    log "  - Integrated System: http://localhost:8080"
    log "  - Clean System: http://localhost:8090"
    log "  - Admin Panel 8080: http://localhost:8080/admin"
    log "  - Admin Panel 8090: http://localhost:8090/admin"
}

# Run main function
main "$@"
