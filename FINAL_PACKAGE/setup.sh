#!/bin/bash

# MesChain Trendyol Integration - Setup Script
# This script prepares the development and production environment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Header
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║              MesChain Trendyol Integration v1.0.0           ║"
echo "║                        Setup Script                         ║"
echo "║                                                              ║"
echo "║  Preparing development and production environment            ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found. Please run this script from the project root."
    exit 1
fi

# Check PHP version
print_status "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

if [ "$PHP_MAJOR" -lt 7 ] || ([ "$PHP_MAJOR" -eq 7 ] && [ "$PHP_MINOR" -lt 4 ]); then
    print_error "PHP 7.4 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi
print_success "PHP version $PHP_VERSION is compatible"

# Check if Composer is installed
print_status "Checking Composer installation..."
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    echo "Visit: https://getcomposer.org/download/"
    exit 1
fi
print_success "Composer is available"

# Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader
print_success "PHP dependencies installed"

# Install development dependencies if requested
if [ "$1" = "--dev" ]; then
    print_status "Installing development dependencies..."
    composer install --optimize-autoloader
    print_success "Development dependencies installed"
fi

# Create necessary directories
print_status "Creating directory structure..."
mkdir -p reports
mkdir -p logs
mkdir -p temp
mkdir -p uploads
print_success "Directory structure created"

# Set permissions
print_status "Setting file permissions..."
chmod +x build.sh
chmod +x deployment/*.sh
chmod +x monitoring/setup_monitoring.sh
print_success "File permissions set"

# Create environment file if it doesn't exist
if [ ! -f ".env" ]; then
    print_status "Creating environment configuration..."
    cat > .env << 'EOF'
# MesChain Trendyol Integration Configuration
# Copy this file and customize for your environment

# Trendyol API Configuration
TRENDYOL_API_URL=https://api.trendyol.com
TRENDYOL_SUPPLIER_ID=your_supplier_id
TRENDYOL_API_KEY=your_api_key
TRENDYOL_API_SECRET=your_api_secret

# Database Configuration
DB_HOST=localhost
DB_NAME=opencart
DB_USER=opencart_user
DB_PASS=opencart_password
DB_PREFIX=oc_

# OpenCart Configuration
OPENCART_URL=https://your-store.com
OPENCART_ADMIN_PATH=admin

# Monitoring Configuration
MONITORING_ENABLED=true
ALERT_EMAIL=admin@your-store.com
SLACK_WEBHOOK_URL=

# Performance Settings
SYNC_BATCH_SIZE=100
API_TIMEOUT=30
MAX_RETRIES=3

# Security Settings
ENABLE_API_RATE_LIMITING=true
API_RATE_LIMIT=1000
ENABLE_REQUEST_LOGGING=true

# Development Settings (set to false in production)
DEBUG_MODE=false
LOG_LEVEL=info
EOF
    print_success "Environment file created (.env)"
    print_warning "Please edit .env file with your actual configuration"
fi

# Run basic health check
print_status "Running basic health check..."

# Check PHP extensions
REQUIRED_EXTENSIONS=("curl" "json" "mbstring" "pdo" "openssl")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "^$ext$"; then
        print_success "PHP extension '$ext' is available"
    else
        print_error "Required PHP extension '$ext' is missing"
        exit 1
    fi
done

# Test autoloader
if php -r "require 'vendor/autoload.php'; echo 'Autoloader works';" &> /dev/null; then
    print_success "Autoloader is working correctly"
else
    print_error "Autoloader test failed"
    exit 1
fi

echo
print_success "Setup completed successfully!"
echo
echo "Next steps:"
echo "1. Edit .env file with your configuration"
echo "2. Run './build.sh --test' to verify installation"
echo "3. Run './deployment/deploy.sh' for production deployment"
echo
echo "For development:"
echo "- Run 'composer test' to run all tests"
echo "- Run 'composer analyze' for static analysis"
echo "- Run './monitoring/setup_monitoring.sh' to setup monitoring"
echo
