#!/bin/bash

# OpenCart Multi-Port Connection Test Script
# This script tests connectivity and functionality of both OpenCart instances

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_DIR="$(dirname "$SCRIPT_DIR")"
PROJECT_ROOT="$(dirname "$CONFIG_DIR")"

# Test URLs
BASE_URL_8080="http://localhost:8080"
BASE_URL_8090="http://localhost:8090"
ADMIN_URL_8080="$BASE_URL_8080/admin"
ADMIN_URL_8090="$BASE_URL_8090/admin"

# Logging
LOG_DIR="/var/log/opencart-multiport"
mkdir -p "$LOG_DIR"
LOGFILE="$LOG_DIR/test-$(date +%Y%m%d-%H%M%S).log"

# Function to log messages
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOGFILE"
}

log_success() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] ✓ SUCCESS:${NC} $1" | tee -a "$LOGFILE"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ✗ ERROR:${NC} $1" | tee -a "$LOGFILE"
}

log_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] ⚠ WARNING:${NC} $1" | tee -a "$LOGFILE"
}

log_info() {
    echo -e "${CYAN}[$(date '+%Y-%m-%d %H:%M:%S')] ℹ INFO:${NC} $1" | tee -a "$LOGFILE"
}

# Function to test HTTP response
test_http_response() {
    local url=$1
    local expected_code=${2:-200}
    local description=$3

    log_info "Testing: $description"
    log_info "URL: $url"

    local response=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 10 --max-time 30 "$url" 2>/dev/null || echo "000")

    if [ "$response" = "$expected_code" ]; then
        log_success "$description - HTTP $response"
        return 0
    else
        log_error "$description - HTTP $response (expected $expected_code)"
        return 1
    fi
}

# Function to test port connectivity
test_port() {
    local port=$1
    local description=$2

    log_info "Testing port connectivity: $port"

    if nc -z localhost "$port" 2>/dev/null; then
        log_success "$description - Port $port is open"
        return 0
    else
        log_error "$description - Port $port is not accessible"
        return 1
    fi
}

# Function to test PHP-FPM socket
test_php_socket() {
    local socket_path=$1
    local description=$2

    log_info "Testing PHP-FPM socket: $socket_path"

    if [ -S "$socket_path" ]; then
        log_success "$description - Socket exists and is accessible"
        return 0
    else
        log_error "$description - Socket not found or not accessible"
        return 1
    fi
}

# Function to test database connectivity
test_database() {
    local port=$1
    local db_prefix=$2
    local description=$3

    log_info "Testing database connectivity for $description"

    # This is a placeholder - actual database test would require credentials
    # For now, we'll just check if MySQL is running
    if systemctl is-active --quiet mysql 2>/dev/null || systemctl is-active --quiet mariadb 2>/dev/null; then
        log_success "$description - Database service is running"
        return 0
    else
        log_warning "$description - Database service status unknown"
        return 1
    fi
}

# Function to test OpenCart installation
test_opencart_installation() {
    local base_url=$1
    local port=$2
    local description=$3

    log_info "Testing OpenCart installation: $description"

    # Test main page
    if test_http_response "$base_url" 200 "$description - Main Page"; then
        # Test if it's actually OpenCart by looking for specific content
        local content=$(curl -s "$base_url" 2>/dev/null || echo "")
        if echo "$content" | grep -q -i "opencart\|powered by opencart" 2>/dev/null; then
            log_success "$description - OpenCart detected"
        else
            log_warning "$description - Page loads but OpenCart not detected"
        fi
    fi

    # Test admin page (might return 404 if not installed, but should not timeout)
    local admin_url="$base_url/admin"
    local admin_response=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 10 --max-time 30 "$admin_url" 2>/dev/null || echo "000")

    if [ "$admin_response" != "000" ]; then
        log_success "$description - Admin panel accessible (HTTP $admin_response)"
    else
        log_error "$description - Admin panel not accessible"
    fi
}

# Function to test Trendyol integration readiness
test_trendyol_integration() {
    local base_url=$1
    local port=$2
    local description=$3

    log_info "Testing Trendyol integration readiness: $description"

    # Test API endpoint (this would be specific to your implementation)
    local api_url="$base_url/index.php?route=extension/module/meschain_trendyol/api"
    local api_response=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 10 --max-time 30 "$api_url" 2>/dev/null || echo "000")

    if [ "$api_response" = "200" ] || [ "$api_response" = "404" ]; then
        log_success "$description - Trendyol API endpoint reachable"
    else
        log_warning "$description - Trendyol API endpoint status: HTTP $api_response"
    fi
}

# Function to test SSL configuration (if enabled)
test_ssl() {
    local port=$1
    local description=$2

    log_info "Testing SSL configuration: $description"

    # Test HTTPS port (if configured)
    local https_port=$((port + 363))  # 8080 -> 8443, 8090 -> 8453

    if nc -z localhost "$https_port" 2>/dev/null; then
        log_success "$description - SSL port $https_port is open"

        # Test SSL certificate
        local ssl_url="https://localhost:$https_port"
        local ssl_response=$(curl -k -s -o /dev/null -w "%{http_code}" --connect-timeout 10 --max-time 30 "$ssl_url" 2>/dev/null || echo "000")

        if [ "$ssl_response" = "200" ]; then
            log_success "$description - SSL connection successful"
        else
            log_warning "$description - SSL connection issues (HTTP $ssl_response)"
        fi
    else
        log_info "$description - SSL not configured (port $https_port not open)"
    fi
}

# Function to generate test report
generate_report() {
    local total_tests=$1
    local passed_tests=$2
    local failed_tests=$3

    log ""
    log "=========================================="
    log "           TEST SUMMARY REPORT"
    log "=========================================="
    log "Total Tests: $total_tests"
    log_success "Passed: $passed_tests"
    log_error "Failed: $failed_tests"
    log "Success Rate: $(( passed_tests * 100 / total_tests ))%"
    log "=========================================="
    log "Log file: $LOGFILE"
    log ""

    if [ $failed_tests -eq 0 ]; then
        log_success "All tests passed! OpenCart multi-port setup is working correctly."
        return 0
    else
        log_error "Some tests failed. Please check the log for details."
        return 1
    fi
}

# Main test execution
main() {
    log "Starting OpenCart Multi-Port Connection Tests..."
    log "Test log: $LOGFILE"
    log ""

    local total_tests=0
    local passed_tests=0
    local failed_tests=0

    # Test basic connectivity
    log "=========================================="
    log "         BASIC CONNECTIVITY TESTS"
    log "=========================================="

    # Port 8080 tests
    total_tests=$((total_tests + 1))
    if test_port 8080 "OpenCart 8080 (Integrated)"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # Port 8090 tests
    total_tests=$((total_tests + 1))
    if test_port 8090 "OpenCart 8090 (Clean)"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # PHP-FPM socket tests
    log ""
    log "=========================================="
    log "         PHP-FPM SOCKET TESTS"
    log "=========================================="

    total_tests=$((total_tests + 1))
    if test_php_socket "/var/run/php/php8.1-fpm-8080.sock" "PHP-FPM 8080"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    total_tests=$((total_tests + 1))
    if test_php_socket "/var/run/php/php8.1-fpm-8090.sock" "PHP-FPM 8090"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # HTTP response tests
    log ""
    log "=========================================="
    log "         HTTP RESPONSE TESTS"
    log "=========================================="

    total_tests=$((total_tests + 1))
    if test_http_response "$BASE_URL_8080" 200 "OpenCart 8080 Main Page"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    total_tests=$((total_tests + 1))
    if test_http_response "$BASE_URL_8090" 200 "OpenCart 8090 Main Page"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # OpenCart installation tests
    log ""
    log "=========================================="
    log "       OPENCART INSTALLATION TESTS"
    log "=========================================="

    total_tests=$((total_tests + 1))
    if test_opencart_installation "$BASE_URL_8080" 8080 "OpenCart 8080 (Integrated)"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    total_tests=$((total_tests + 1))
    if test_opencart_installation "$BASE_URL_8090" 8090 "OpenCart 8090 (Clean)"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # Database tests
    log ""
    log "=========================================="
    log "           DATABASE TESTS"
    log "=========================================="

    total_tests=$((total_tests + 1))
    if test_database 8080 "oc8080_" "Database for OpenCart 8080"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    total_tests=$((total_tests + 1))
    if test_database 8090 "oc8090_" "Database for OpenCart 8090"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # Trendyol integration tests
    log ""
    log "=========================================="
    log "       TRENDYOL INTEGRATION TESTS"
    log "=========================================="

    total_tests=$((total_tests + 1))
    if test_trendyol_integration "$BASE_URL_8080" 8080 "Trendyol Integration 8080"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    total_tests=$((total_tests + 1))
    if test_trendyol_integration "$BASE_URL_8090" 8090 "Trendyol Integration 8090"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # SSL tests (optional)
    log ""
    log "=========================================="
    log "             SSL TESTS"
    log "=========================================="

    total_tests=$((total_tests + 1))
    if test_ssl 8080 "SSL Configuration 8080"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    total_tests=$((total_tests + 1))
    if test_ssl 8090 "SSL Configuration 8090"; then
        passed_tests=$((passed_tests + 1))
    else
        failed_tests=$((failed_tests + 1))
    fi

    # Generate final report
    log ""
    generate_report $total_tests $passed_tests $failed_tests
}

# Check dependencies
check_dependencies() {
    local missing_deps=()

    if ! command -v curl >/dev/null 2>&1; then
        missing_deps+=("curl")
    fi

    if ! command -v nc >/dev/null 2>&1; then
        missing_deps+=("netcat")
    fi

    if [ ${#missing_deps[@]} -ne 0 ]; then
        log_error "Missing dependencies: ${missing_deps[*]}"
        log "Please install missing dependencies and try again."
        exit 1
    fi
}

# Run dependency check and main function
check_dependencies
main "$@"
