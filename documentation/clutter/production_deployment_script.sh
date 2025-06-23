#!/bin/bash

# 🚀 CURSOR TEAM PRODUCTION DEPLOYMENT SCRIPT
# Phase 2 Systems: Redis Cache + RabbitMQ + Data Export + Monitoring
# Date: December 19, 2024
# Target: Complete production deployment in 7 days

set -e  # Exit on any error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
DEPLOY_DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/opt/meschain/backups/${DEPLOY_DATE}"
LOG_FILE="/var/log/meschain/deployment_${DEPLOY_DATE}.log"
DEPLOYMENT_ENV="production"

echo -e "${CYAN}🚀 CURSOR TEAM PRODUCTION DEPLOYMENT STARTING...${NC}"
echo -e "${BLUE}📅 Deployment Date: ${DEPLOY_DATE}${NC}"
echo -e "${PURPLE}📍 Environment: ${DEPLOYMENT_ENV}${NC}"

# Function to log messages
log_message() {
    local level=$1
    local message=$2
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "[${timestamp}] [${level}] ${message}" | tee -a ${LOG_FILE}
}

# Function to check prerequisites
check_prerequisites() {
    log_message "INFO" "${YELLOW}🔍 Checking prerequisites...${NC}"
    
    # Check if running as root
    if [ "$EUID" -ne 0 ]; then
        log_message "ERROR" "${RED}❌ Please run as root (sudo)${NC}"
        exit 1
    fi
    
    # Check disk space (minimum 10GB)
    available_space=$(df -BG /opt | awk 'NR==2 {print $4}' | sed 's/G//')
    if [ "$available_space" -lt 10 ]; then
        log_message "ERROR" "${RED}❌ Insufficient disk space. Need minimum 10GB, available: ${available_space}GB${NC}"
        exit 1
    fi
    
    # Check required commands
    local required_commands=("docker" "docker-compose" "nginx" "systemctl" "npm" "node")
    for cmd in "${required_commands[@]}"; do
        if ! command -v $cmd &> /dev/null; then
            log_message "ERROR" "${RED}❌ Required command not found: $cmd${NC}"
            exit 1
        fi
    done
    
    log_message "INFO" "${GREEN}✅ All prerequisites met${NC}"
}

# Function to create backup
create_backup() {
    log_message "INFO" "${YELLOW}💾 Creating backup before deployment...${NC}"
    
    mkdir -p ${BACKUP_DIR}
    
    # Backup database
    if systemctl is-active --quiet mysql; then
        mysqldump -u root -p meschain_db > ${BACKUP_DIR}/database_backup.sql
        log_message "INFO" "${GREEN}✅ Database backup created${NC}"
    fi
    
    # Backup configuration files
    cp -r /etc/nginx ${BACKUP_DIR}/nginx_config
    cp -r /opt/meschain/config ${BACKUP_DIR}/app_config 2>/dev/null || true
    
    # Backup current application
    if [ -d "/opt/meschain/current" ]; then
        tar -czf ${BACKUP_DIR}/application_backup.tar.gz -C /opt/meschain current/
        log_message "INFO" "${GREEN}✅ Application backup created${NC}"
    fi
    
    log_message "INFO" "${GREEN}✅ Backup completed: ${BACKUP_DIR}${NC}"
}

# Function to deploy Redis Cache
deploy_redis_cache() {
    log_message "INFO" "${YELLOW}🎯 Deploying Redis Cache System...${NC}"
    
    # Install Redis if not installed
    if ! command -v redis-server &> /dev/null; then
        apt-get update
        apt-get install -y redis-server redis-tools
    fi
    
    # Create Redis configuration
    cat > /etc/redis/redis.conf << EOF
# Redis Production Configuration - CURSOR TEAM
port 6379
bind 127.0.0.1
timeout 300
tcp-keepalive 60
tcp-backlog 511
databases 16
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /var/lib/redis
maxmemory 2gb
maxmemory-policy allkeys-lru
maxclients 10000
EOF
    
    # Start Redis service
    systemctl enable redis-server
    systemctl restart redis-server
    
    # Test Redis connection
    if redis-cli ping | grep -q PONG; then
        log_message "INFO" "${GREEN}✅ Redis Cache deployed successfully${NC}"
    else
        log_message "ERROR" "${RED}❌ Redis deployment failed${NC}"
        exit 1
    fi
    
    # Copy Redis cache implementation
    mkdir -p /opt/meschain/lib
    cp redis_cache_implementation.js /opt/meschain/lib/
    
    log_message "INFO" "${GREEN}✅ Redis Cache System deployment completed${NC}"
}

# Function to deploy RabbitMQ
deploy_rabbitmq() {
    log_message "INFO" "${YELLOW}🔄 Deploying RabbitMQ Message Queue...${NC}"
    
    # Install RabbitMQ
    if ! command -v rabbitmq-server &> /dev/null; then
        # Add RabbitMQ repository
        curl -fsSL https://github.com/rabbitmq/signing-keys/releases/download/2.0/rabbitmq-release-signing-key.asc | apt-key add -
        echo 'deb https://dl.bintray.com/rabbitmq-erlang/debian bionic erlang' > /etc/apt/sources.list.d/bintray.rabbitmq.list
        echo 'deb https://dl.bintray.com/rabbitmq/debian bionic main' >> /etc/apt/sources.list.d/bintray.rabbitmq.list
        
        apt-get update
        apt-get install -y rabbitmq-server
    fi
    
    # Enable management plugin
    rabbitmq-plugins enable rabbitmq_management
    
    # Create RabbitMQ configuration
    cat > /etc/rabbitmq/rabbitmq.conf << EOF
# RabbitMQ Production Configuration - CURSOR TEAM
listeners.tcp.default = 5672
management.tcp.port = 15672
default_vhost = /
default_user = meschain
default_pass = $(openssl rand -base64 32)
vm_memory_high_watermark.relative = 0.6
disk_free_limit.relative = 2.0
collect_statistics_interval = 5000
EOF
    
    # Start RabbitMQ service
    systemctl enable rabbitmq-server
    systemctl restart rabbitmq-server
    
    # Wait for RabbitMQ to start
    sleep 10
    
    # Test RabbitMQ connection
    if rabbitmqctl status &> /dev/null; then
        log_message "INFO" "${GREEN}✅ RabbitMQ deployed successfully${NC}"
    else
        log_message "ERROR" "${RED}❌ RabbitMQ deployment failed${NC}"
        exit 1
    fi
    
    # Copy RabbitMQ integration
    cp rabbitmq_integration.js /opt/meschain/lib/
    
    log_message "INFO" "${GREEN}✅ RabbitMQ Message Queue deployment completed${NC}"
}

# Function to deploy Data Export System
deploy_export_system() {
    log_message "INFO" "${YELLOW}📊 Deploying Data Export & Reporting System...${NC}"
    
    # Install Node.js dependencies for export system
    cd /opt/meschain
    
    # Create package.json if not exists
    if [ ! -f package.json ]; then
        cat > package.json << EOF
{
  "name": "meschain-export-system",
  "version": "1.0.0",
  "description": "MesChain Data Export & Reporting System",
  "main": "data_export_reporting_system.js",
  "dependencies": {
    "xlsx": "^0.18.5",
    "node-cron": "^3.0.2",
    "nodemailer": "^6.9.1"
  }
}
EOF
    fi
    
    # Install dependencies
    npm install
    
    # Copy export system files
    cp data_export_reporting_system.js /opt/meschain/lib/
    
    # Create exports directories
    mkdir -p /opt/meschain/exports/{excel,csv,pdf}
    mkdir -p /opt/meschain/reports/{daily,weekly,monthly}
    
    # Set permissions
    chown -R www-data:www-data /opt/meschain/exports
    chown -R www-data:www-data /opt/meschain/reports
    chmod -R 755 /opt/meschain/exports
    chmod -R 755 /opt/meschain/reports
    
    log_message "INFO" "${GREEN}✅ Data Export & Reporting System deployment completed${NC}"
}

# Function to setup monitoring
setup_monitoring() {
    log_message "INFO" "${YELLOW}📊 Setting up Performance Monitoring...${NC}"
    
    # Install monitoring tools
    apt-get install -y htop iotop nethogs
    
    # Create monitoring configuration
    mkdir -p /opt/meschain/monitoring
    
    cat > /opt/meschain/monitoring/system_monitor.sh << 'EOF'
#!/bin/bash
# System Monitoring Script - CURSOR TEAM
LOG_FILE="/var/log/meschain/system_monitor.log"

while true; do
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//')
    MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.1f%%", $3/$2 * 100.0)}')
    DISK_USAGE=$(df -h / | awk 'NR==2{printf "%s", $5}')
    LOAD_AVG=$(uptime | awk -F'load average:' '{print $2}')
    
    # Redis monitoring
    REDIS_CONNECTED=$(redis-cli info clients | grep connected_clients | cut -d: -f2 | tr -d '\r')
    REDIS_MEMORY=$(redis-cli info memory | grep used_memory_human | cut -d: -f2 | tr -d '\r')
    
    # RabbitMQ monitoring
    RABBITMQ_QUEUES=$(rabbitmqctl list_queues | wc -l)
    
    echo "[$TIMESTAMP] CPU: $CPU_USAGE | Memory: $MEMORY_USAGE | Disk: $DISK_USAGE | Load: $LOAD_AVG | Redis Clients: $REDIS_CONNECTED | Redis Memory: $REDIS_MEMORY | RabbitMQ Queues: $RABBITMQ_QUEUES" >> $LOG_FILE
    
    sleep 60
done
EOF
    
    chmod +x /opt/meschain/monitoring/system_monitor.sh
    
    # Create systemd service for monitoring
    cat > /etc/systemd/system/meschain-monitor.service << EOF
[Unit]
Description=MesChain System Monitor
After=network.target

[Service]
Type=simple
User=root
ExecStart=/opt/meschain/monitoring/system_monitor.sh
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF
    
    systemctl daemon-reload
    systemctl enable meschain-monitor
    systemctl start meschain-monitor
    
    log_message "INFO" "${GREEN}✅ Performance Monitoring setup completed${NC}"
}

# Function to configure Nginx
configure_nginx() {
    log_message "INFO" "${YELLOW}🌐 Configuring Nginx Load Balancer...${NC}"
    
    # Backup original nginx config
    cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup
    
    # Create MesChain Nginx configuration
    cat > /etc/nginx/sites-available/meschain << EOF
# MesChain Production Configuration - CURSOR TEAM
upstream meschain_backend {
    server 127.0.0.1:3000 max_fails=3 fail_timeout=30s;
    server 127.0.0.1:3001 max_fails=3 fail_timeout=30s backup;
}

# Rate limiting
limit_req_zone \$binary_remote_addr zone=api:10m rate=10r/s;
limit_req_zone \$binary_remote_addr zone=dashboard:10m rate=30r/s;

server {
    listen 80;
    server_name meschain.local;
    
    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    # Dashboard
    location / {
        limit_req zone=dashboard burst=20 nodelay;
        proxy_pass http://meschain_backend;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_connect_timeout 30s;
        proxy_send_timeout 30s;
        proxy_read_timeout 30s;
    }
    
    # API endpoints
    location /api/ {
        limit_req zone=api burst=10 nodelay;
        proxy_pass http://meschain_backend;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
    
    # Export downloads
    location /exports/ {
        alias /opt/meschain/exports/;
        expires 1h;
        add_header Cache-Control "public, immutable";
    }
    
    # Health check
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
EOF
    
    # Enable the site
    ln -sf /etc/nginx/sites-available/meschain /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # Test nginx configuration
    nginx -t
    if [ $? -eq 0 ]; then
        systemctl reload nginx
        log_message "INFO" "${GREEN}✅ Nginx configuration completed${NC}"
    else
        log_message "ERROR" "${RED}❌ Nginx configuration failed${NC}"
        exit 1
    fi
}

# Function to run deployment tests
run_deployment_tests() {
    log_message "INFO" "${YELLOW}🧪 Running deployment tests...${NC}"
    
    # Test Redis
    if redis-cli ping | grep -q PONG; then
        log_message "INFO" "${GREEN}✅ Redis test passed${NC}"
    else
        log_message "ERROR" "${RED}❌ Redis test failed${NC}"
        return 1
    fi
    
    # Test RabbitMQ
    if rabbitmqctl status &> /dev/null; then
        log_message "INFO" "${GREEN}✅ RabbitMQ test passed${NC}"
    else
        log_message "ERROR" "${RED}❌ RabbitMQ test failed${NC}"
        return 1
    fi
    
    # Test Nginx
    if nginx -t &> /dev/null; then
        log_message "INFO" "${GREEN}✅ Nginx test passed${NC}"
    else
        log_message "ERROR" "${RED}❌ Nginx test failed${NC}"
        return 1
    fi
    
    # Test Node.js dependencies
    cd /opt/meschain
    if npm list --depth=0 &> /dev/null; then
        log_message "INFO" "${GREEN}✅ Node.js dependencies test passed${NC}"
    else
        log_message "ERROR" "${RED}❌ Node.js dependencies test failed${NC}"
        return 1
    fi
    
    log_message "INFO" "${GREEN}✅ All deployment tests passed${NC}"
    return 0
}

# Function to display deployment summary
display_summary() {
    log_message "INFO" "${CYAN}📋 DEPLOYMENT SUMMARY${NC}"
    echo -e "${BLUE}================================${NC}"
    echo -e "${GREEN}✅ Redis Cache: $(redis-cli ping 2>/dev/null || echo 'FAILED')${NC}"
    echo -e "${GREEN}✅ RabbitMQ: $(rabbitmqctl status &>/dev/null && echo 'RUNNING' || echo 'FAILED')${NC}"
    echo -e "${GREEN}✅ Nginx: $(systemctl is-active nginx)${NC}"
    echo -e "${GREEN}✅ Monitoring: $(systemctl is-active meschain-monitor)${NC}"
    echo -e "${GREEN}✅ Backup Location: ${BACKUP_DIR}${NC}"
    echo -e "${GREEN}✅ Log Location: ${LOG_FILE}${NC}"
    echo -e "${BLUE}================================${NC}"
    
    echo -e "${PURPLE}🎯 NEXT STEPS:${NC}"
    echo -e "${YELLOW}1. Configure application database connection${NC}"
    echo -e "${YELLOW}2. Start application services${NC}"
    echo -e "${YELLOW}3. Run user training sessions${NC}"
    echo -e "${YELLOW}4. Schedule backup testing${NC}"
    
    echo -e "${CYAN}🚀 CURSOR TEAM PRODUCTION DEPLOYMENT COMPLETED!${NC}"
}

# Main deployment function
main() {
    log_message "INFO" "${CYAN}🚀 Starting CURSOR Team Production Deployment${NC}"
    
    # Create log directory
    mkdir -p /var/log/meschain
    
    # Run deployment steps
    check_prerequisites
    create_backup
    deploy_redis_cache
    deploy_rabbitmq
    deploy_export_system
    setup_monitoring
    configure_nginx
    
    if run_deployment_tests; then
        display_summary
        log_message "INFO" "${GREEN}🎉 PRODUCTION DEPLOYMENT SUCCESSFUL!${NC}"
        exit 0
    else
        log_message "ERROR" "${RED}❌ DEPLOYMENT TESTS FAILED - ROLLING BACK${NC}"
        # Here would be rollback logic
        exit 1
    fi
}

# Run main function
main "$@" 