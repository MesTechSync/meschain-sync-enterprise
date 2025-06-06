#!/bin/sh
# 🏥 MesChain-Sync Enterprise - Health Check Script
# Comprehensive health monitoring for production deployment

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_PORT=${APP_PORT:-8080}
APP_HOST=${APP_HOST:-localhost}
HEALTH_ENDPOINT="/health"
TIMEOUT=10
MAX_RETRIES=3

# Log function
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') [HEALTH] $1"
}

# Success function
success() {
    echo "${GREEN}✅ $1${NC}"
    log "SUCCESS: $1"
}

# Warning function
warning() {
    echo "${YELLOW}⚠️  $1${NC}"
    log "WARNING: $1"
}

# Error function
error() {
    echo "${RED}❌ $1${NC}"
    log "ERROR: $1"
}

# Health check function
check_health() {
    local url="http://${APP_HOST}:${APP_PORT}${HEALTH_ENDPOINT}"
    local attempt=1
    
    log "Starting health check for ${url}"
    
    while [ $attempt -le $MAX_RETRIES ]; do
        log "Health check attempt ${attempt}/${MAX_RETRIES}"
        
        # Perform the health check
        response=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout $TIMEOUT "$url" 2>/dev/null)
        
        if [ "$response" = "200" ]; then
            success "Health check passed (HTTP $response)"
            return 0
        else
            warning "Health check failed (HTTP $response) - Attempt ${attempt}/${MAX_RETRIES}"
            if [ $attempt -lt $MAX_RETRIES ]; then
                sleep 2
            fi
        fi
        
        attempt=$((attempt + 1))
    done
    
    error "Health check failed after ${MAX_RETRIES} attempts"
    return 1
}

# Check if curl is available
check_dependencies() {
    if ! command -v curl >/dev/null 2>&1; then
        error "curl is required but not installed"
        return 1
    fi
    return 0
}

# Check application process
check_process() {
    if pgrep nginx >/dev/null 2>&1; then
        success "Nginx process is running"
        return 0
    else
        error "Nginx process is not running"
        return 1
    fi
}

# Check disk space
check_disk_space() {
    local threshold=90
    local usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
    
    if [ "$usage" -lt "$threshold" ]; then
        success "Disk usage is healthy (${usage}%)"
        return 0
    else
        warning "Disk usage is high (${usage}%)"
        return 1
    fi
}

# Check memory usage
check_memory() {
    local threshold=90
    local usage=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
    
    if [ "$usage" -lt "$threshold" ]; then
        success "Memory usage is healthy (${usage}%)"
        return 0
    else
        warning "Memory usage is high (${usage}%)"
        return 1
    fi
}

# Check log files
check_logs() {
    local error_log="/var/log/nginx/meschain.error.log"
    local access_log="/var/log/nginx/meschain.access.log"
    
    # Check if logs are being written (modified in last 5 minutes)
    if [ -f "$access_log" ] && [ $(find "$access_log" -mmin -5 | wc -l) -gt 0 ]; then
        success "Access logs are being written"
    else
        warning "Access logs may not be updating"
    fi
    
    # Check for recent errors (last 100 lines)
    if [ -f "$error_log" ]; then
        local recent_errors=$(tail -100 "$error_log" 2>/dev/null | grep -i error | wc -l)
        if [ "$recent_errors" -eq 0 ]; then
            success "No recent errors in logs"
        else
            warning "Found $recent_errors recent errors in logs"
        fi
    fi
    
    return 0
}

# Main health check execution
main() {
    log "=== MesChain-Sync Enterprise Health Check Started ==="
    
    local exit_code=0
    
    # Check dependencies
    if ! check_dependencies; then
        exit_code=1
    fi
    
    # Check if nginx process is running
    if ! check_process; then
        exit_code=1
    fi
    
    # Perform HTTP health check
    if ! check_health; then
        exit_code=1
    fi
    
    # Check system resources
    check_disk_space || exit_code=1
    check_memory || exit_code=1
    
    # Check logs
    check_logs
    
    # Final result
    if [ $exit_code -eq 0 ]; then
        success "=== All health checks passed ==="
        log "Health check completed successfully"
    else
        error "=== Some health checks failed ==="
        log "Health check completed with errors"
    fi
    
    return $exit_code
}

# Handle script termination
trap 'log "Health check interrupted"; exit 1' INT TERM

# Execute main function
main "$@" 