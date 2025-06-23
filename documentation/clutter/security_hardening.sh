#!/bin/bash
# ================================================
# MesChain-Sync Security Hardening Script
# Version: 3.0.4.0
# Author: Musti - DevOps & Infrastructure Team
# Date: 2025-01-05
# ================================================

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        error "This script should not be run as root for security reasons"
        exit 1
    fi
}

# Environment detection
detect_environment() {
    local env_file="config/environment.conf"
    
    if [[ -f "$env_file" ]]; then
        source "$env_file"
        ENVIRONMENT=${ENVIRONMENT:-"production"}
    else
        ENVIRONMENT="production"
    fi
    
    log "Detected environment: $ENVIRONMENT"
}

# ================================================
# 1. FILE PERMISSIONS HARDENING
# ================================================

harden_file_permissions() {
    log "🔒 Hardening file permissions..."
    
    local web_root="upload"
    
    # Set secure permissions for web root
    find "$web_root" -type f -exec chmod 644 {} \;
    find "$web_root" -type d -exec chmod 755 {} \;
    
    # Secure configuration files
    if [[ -f "$web_root/config.php" ]]; then
        chmod 600 "$web_root/config.php"
        log "Secured config.php permissions"
    fi
    
    if [[ -f "$web_root/admin/config.php" ]]; then
        chmod 600 "$web_root/admin/config.php"
        log "Secured admin/config.php permissions"
    fi
    
    # Secure cache and log directories
    local cache_dir="$web_root/system/storage/cache"
    local log_dir="$web_root/system/storage/logs"
    
    if [[ -d "$cache_dir" ]]; then
        chmod 755 "$cache_dir"
        find "$cache_dir" -type f -exec chmod 644 {} \;
    fi
    
    if [[ -d "$log_dir" ]]; then
        chmod 755 "$log_dir"
        find "$log_dir" -type f -exec chmod 644 {} \;
    fi
    
    # Remove world-writable permissions
    find "$web_root" -type f -perm 777 -exec chmod 755 {} \;
    find "$web_root" -type d -perm 777 -exec chmod 755 {} \;
    
    log "✅ File permissions hardened"
}

# ================================================
# 2. SECURE FILE CLEANUP
# ================================================

secure_file_cleanup() {
    log "🧹 Performing secure file cleanup..."
    
    local web_root="upload"
    
    # Remove dangerous files
    local dangerous_files=(
        ".git"
        ".gitignore"
        ".env"
        ".env.local"
        ".env.production"
        "composer.json"
        "composer.lock"
        "package.json"
        "package-lock.json"
        "yarn.lock"
        "Dockerfile"
        "docker-compose.yml"
        ".htpasswd"
        "phpinfo.php"
        "test.php"
        "debug.php"
        "*.bak"
        "*.backup"
        "*.old"
        "*.tmp"
    )
    
    for pattern in "${dangerous_files[@]}"; do
        find "$web_root" -name "$pattern" -type f -delete 2>/dev/null || true
        find "$web_root" -name "$pattern" -type d -exec rm -rf {} \; 2>/dev/null || true
    done
    
    # Remove empty directories
    find "$web_root" -type d -empty -delete 2>/dev/null || true
    
    log "✅ Secure file cleanup completed"
}

# ================================================
# 3. APACHE/NGINX SECURITY HEADERS
# ================================================

configure_web_server_security() {
    log "🛡️ Configuring web server security..."
    
    local htaccess_file="upload/.htaccess"
    
    # Create secure .htaccess file
    cat > "$htaccess_file" << 'EOF'
# MesChain-Sync Security Configuration
# Generated automatically - Do not edit manually

# Disable server signature
ServerSignature Off

# Hide PHP version
Header unset X-Powered-By
Header always unset X-Powered-By

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "camera=(), microphone=(), location=(), payment=()"

# Content Security Policy
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https:; frame-ancestors 'none';"

# HSTS (HTTP Strict Transport Security)
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"

# Disable directory browsing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|sql|conf|bak|backup|old|tmp)$">
    Require all denied
</FilesMatch>

# Prevent access to version control
<DirectoryMatch "\.git">
    Require all denied
</DirectoryMatch>

# Prevent PHP execution in upload directories
<Directory "*/image/*">
    php_flag engine off
</Directory>

<Directory "*/download/*">
    php_flag engine off
</Directory>

# Rate limiting (if mod_reqtimeout is available)
<IfModule mod_reqtimeout.c>
    RequestReadTimeout header=20-40,MinRate=500 body=20,MinRate=500
</IfModule>

# Prevent hotlinking
RewriteEngine On
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?yourdomain.com [NC]
RewriteRule \.(jpg|jpeg|png|gif|css|js)$ - [F,L]

# Block suspicious requests
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=http:// [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=https:// [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=(\.\.//?)+ [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=/([a-z0-9_.]//?)+ [NC]
RewriteRule .* - [F]

# Block common attack patterns
RewriteCond %{QUERY_STRING} (<|%3C).*script.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} proc/self/environ [OR]
RewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|\%3D) [OR]
RewriteCond %{QUERY_STRING} base64_(en|de)code[^(]*\([^)]*\) [OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\|%7C) [NC]
RewriteRule .* - [F]

EOF

    log "✅ Web server security headers configured"
}

# ================================================
# 4. DATABASE SECURITY
# ================================================

secure_database_configuration() {
    log "🗄️ Securing database configuration..."
    
    # Generate secure database password if needed
    local db_config_file="config/database.conf"
    
    if [[ ! -f "$db_config_file" ]]; then
        mkdir -p config
        
        # Generate random password
        local db_password=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
        
        cat > "$db_config_file" << EOF
# MesChain-Sync Database Configuration
DB_HOST=localhost
DB_NAME=meschain_sync
DB_USER=meschain_app
DB_PASSWORD=$db_password
DB_PREFIX=oc_
DB_CHARSET=utf8
DB_COLLATION=utf8_general_ci
EOF
        
        chmod 600 "$db_config_file"
        log "Generated secure database configuration"
    fi
    
    # Create database security script
    cat > "config/db_security.sql" << 'EOF'
-- Database Security Hardening

-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Secure root account
UPDATE mysql.user SET Host='localhost' WHERE User='root' AND Host!='localhost';

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Set secure password policy
SET GLOBAL validate_password.policy = STRONG;
SET GLOBAL validate_password.length = 12;
SET GLOBAL validate_password.mixed_case_count = 1;
SET GLOBAL validate_password.number_count = 1;
SET GLOBAL validate_password.special_char_count = 1;

-- Disable remote root login
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Flush privileges
FLUSH PRIVILEGES;
EOF
    
    log "✅ Database security configuration created"
}

# ================================================
# 5. SSL/TLS CONFIGURATION
# ================================================

configure_ssl_security() {
    log "🔐 Configuring SSL/TLS security..."
    
    local ssl_config_dir="config/ssl"
    mkdir -p "$ssl_config_dir"
    
    # Create SSL configuration template
    cat > "$ssl_config_dir/ssl_config.conf" << 'EOF'
# SSL/TLS Security Configuration Template

# Apache SSL Configuration
<IfModule mod_ssl.c>
    # Modern SSL Configuration
    SSLEngine on
    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    SSLHonorCipherOrder on
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384
    
    # OCSP Stapling
    SSLUseStapling on
    SSLStaplingCache shmcb:/var/run/ocsp(128000)
    
    # Certificate files (update paths as needed)
    # SSLCertificateFile /path/to/certificate.crt
    # SSLCertificateKeyFile /path/to/private.key
    # SSLCertificateChainFile /path/to/chain.crt
</IfModule>

# Nginx SSL Configuration
# server {
#     listen 443 ssl http2;
#     
#     # Modern SSL Configuration
#     ssl_protocols TLSv1.2 TLSv1.3;
#     ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
#     ssl_prefer_server_ciphers off;
#     
#     # OCSP Stapling
#     ssl_stapling on;
#     ssl_stapling_verify on;
#     
#     # Certificate files (update paths as needed)
#     # ssl_certificate /path/to/certificate.crt;
#     # ssl_certificate_key /path/to/private.key;
# }
EOF

    # Create Let's Encrypt setup script
    cat > "$ssl_config_dir/setup_letsencrypt.sh" << 'EOF'
#!/bin/bash
# Let's Encrypt SSL Certificate Setup

DOMAIN="your-domain.com"
EMAIL="admin@your-domain.com"

# Install certbot (Ubuntu/Debian)
if command -v apt-get >/dev/null 2>&1; then
    sudo apt-get update
    sudo apt-get install -y certbot python3-certbot-apache
fi

# Install certbot (CentOS/RHEL)
if command -v yum >/dev/null 2>&1; then
    sudo yum install -y certbot python3-certbot-apache
fi

# Generate certificate
sudo certbot --apache -d "$DOMAIN" --email "$EMAIL" --agree-tos --non-interactive

# Setup auto-renewal
(sudo crontab -l 2>/dev/null; echo "0 12 * * * /usr/bin/certbot renew --quiet") | sudo crontab -

echo "SSL certificate setup completed for $DOMAIN"
EOF

    chmod +x "$ssl_config_dir/setup_letsencrypt.sh"
    
    log "✅ SSL/TLS configuration templates created"
}

# ================================================
# 6. FIREWALL CONFIGURATION
# ================================================

configure_firewall() {
    log "🔥 Configuring firewall rules..."
    
    local firewall_script="config/firewall_setup.sh"
    
    cat > "$firewall_script" << 'EOF'
#!/bin/bash
# Firewall Configuration for MesChain-Sync

# UFW Configuration (Ubuntu/Debian)
if command -v ufw >/dev/null 2>&1; then
    echo "Configuring UFW firewall..."
    
    # Reset to defaults
    sudo ufw --force reset
    
    # Default policies
    sudo ufw default deny incoming
    sudo ufw default allow outgoing
    
    # Allow SSH (change port if needed)
    sudo ufw allow 22/tcp
    
    # Allow HTTP/HTTPS
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    
    # Allow MySQL (only from localhost)
    sudo ufw allow from 127.0.0.1 to any port 3306
    
    # Rate limiting for SSH
    sudo ufw limit 22/tcp
    
    # Enable firewall
    sudo ufw --force enable
    
    echo "UFW firewall configured successfully"
fi

# iptables Configuration (CentOS/RHEL)
if command -v iptables >/dev/null 2>&1 && ! command -v ufw >/dev/null 2>&1; then
    echo "Configuring iptables firewall..."
    
    # Flush existing rules
    sudo iptables -F
    sudo iptables -X
    sudo iptables -t nat -F
    sudo iptables -t nat -X
    sudo iptables -t mangle -F
    sudo iptables -t mangle -X
    
    # Default policies
    sudo iptables -P INPUT DROP
    sudo iptables -P FORWARD DROP
    sudo iptables -P OUTPUT ACCEPT
    
    # Allow loopback
    sudo iptables -A INPUT -i lo -j ACCEPT
    sudo iptables -A OUTPUT -o lo -j ACCEPT
    
    # Allow established connections
    sudo iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
    
    # Allow SSH (with rate limiting)
    sudo iptables -A INPUT -p tcp --dport 22 -m state --state NEW -m limit --limit 3/min --limit-burst 3 -j ACCEPT
    
    # Allow HTTP/HTTPS
    sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
    sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
    
    # Allow MySQL from localhost only
    sudo iptables -A INPUT -p tcp -s 127.0.0.1 --dport 3306 -j ACCEPT
    
    # Save rules
    if command -v iptables-save >/dev/null 2>&1; then
        sudo iptables-save > /etc/iptables/rules.v4
    fi
    
    echo "iptables firewall configured successfully"
fi
EOF

    chmod +x "$firewall_script"
    
    log "✅ Firewall configuration script created"
}

# ================================================
# 7. MONITORING AND ALERTING
# ================================================

setup_security_monitoring() {
    log "📊 Setting up security monitoring..."
    
    local monitoring_dir="config/monitoring"
    mkdir -p "$monitoring_dir"
    
    # Create fail2ban configuration
    cat > "$monitoring_dir/jail.local" << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3
backend = auto

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3

[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/error.log
maxretry = 3

[apache-badbots]
enabled = true
port = http,https
filter = apache-badbots
logpath = /var/log/apache2/access.log
maxretry = 2

[apache-overflows]
enabled = true
port = http,https
filter = apache-overflows
logpath = /var/log/apache2/error.log
maxretry = 2
EOF

    # Create log monitoring script
    cat > "$monitoring_dir/log_monitor.sh" << 'EOF'
#!/bin/bash
# Security Log Monitoring Script

LOG_FILE="/var/log/meschain_security.log"
ALERT_EMAIL="admin@example.com"

# Monitor for suspicious activities
tail -f /var/log/apache2/access.log | while read line; do
    # Check for SQL injection attempts
    if echo "$line" | grep -i "union\|select\|insert\|update\|delete\|drop\|create" >/dev/null; then
        echo "[$(date)] SQL injection attempt detected: $line" >> "$LOG_FILE"
        echo "SQL injection attempt detected on $(hostname)" | mail -s "Security Alert" "$ALERT_EMAIL"
    fi
    
    # Check for XSS attempts
    if echo "$line" | grep -i "script\|javascript\|onload\|onerror" >/dev/null; then
        echo "[$(date)] XSS attempt detected: $line" >> "$LOG_FILE"
        echo "XSS attempt detected on $(hostname)" | mail -s "Security Alert" "$ALERT_EMAIL"
    fi
    
    # Check for directory traversal
    if echo "$line" | grep -i "\.\./\|\.\.\\\" >/dev/null; then
        echo "[$(date)] Directory traversal attempt detected: $line" >> "$LOG_FILE"
        echo "Directory traversal attempt detected on $(hostname)" | mail -s "Security Alert" "$ALERT_EMAIL"
    fi
done &
EOF

    chmod +x "$monitoring_dir/log_monitor.sh"
    
    log "✅ Security monitoring setup completed"
}

# ================================================
# 8. BACKUP SECURITY
# ================================================

setup_secure_backup() {
    log "💾 Setting up secure backup system..."
    
    local backup_script="config/secure_backup.sh"
    
    cat > "$backup_script" << 'EOF'
#!/bin/bash
# Secure Backup Script for MesChain-Sync

BACKUP_DIR="/var/backups/meschain"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30
GPG_RECIPIENT="admin@example.com"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Database backup
mysqldump --single-transaction --routines --triggers meschain_sync | gzip > "$BACKUP_DIR/db_backup_$DATE.sql.gz"

# File backup
tar -czf "$BACKUP_DIR/files_backup_$DATE.tar.gz" upload/

# Encrypt backups
if command -v gpg >/dev/null 2>&1; then
    gpg --trust-model always -r "$GPG_RECIPIENT" --encrypt "$BACKUP_DIR/db_backup_$DATE.sql.gz"
    gpg --trust-model always -r "$GPG_RECIPIENT" --encrypt "$BACKUP_DIR/files_backup_$DATE.tar.gz"
    
    # Remove unencrypted files
    rm "$BACKUP_DIR/db_backup_$DATE.sql.gz"
    rm "$BACKUP_DIR/files_backup_$DATE.tar.gz"
fi

# Clean old backups
find "$BACKUP_DIR" -name "*.gpg" -mtime +$RETENTION_DAYS -delete

# Log backup completion
echo "[$(date)] Backup completed successfully" >> /var/log/meschain_backup.log
EOF

    chmod +x "$backup_script"
    
    # Create backup cron job
    cat > "config/backup_cron" << 'EOF'
# MesChain-Sync Backup Cron Job
# Run backup daily at 2 AM
0 2 * * * /path/to/meschain-sync/config/secure_backup.sh
EOF

    log "✅ Secure backup system configured"
}

# ================================================
# 9. SECURITY AUDIT
# ================================================

run_security_audit() {
    log "🔍 Running security audit..."
    
    local audit_report="security_audit_$(date +%Y%m%d_%H%M%S).txt"
    
    {
        echo "MesChain-Sync Security Audit Report"
        echo "Generated: $(date)"
        echo "========================================"
        echo
        
        echo "1. File Permissions Check:"
        echo "-------------------------"
        find upload -type f \( -perm 777 -o -perm 666 -o -perm 755 \) -ls
        echo
        
        echo "2. Configuration Files Check:"
        echo "----------------------------"
        if [[ -f "upload/config.php" ]]; then
            echo "config.php permissions: $(stat -c %a upload/config.php)"
        fi
        if [[ -f "upload/admin/config.php" ]]; then
            echo "admin/config.php permissions: $(stat -c %a upload/admin/config.php)"
        fi
        echo
        
        echo "3. Sensitive Files Check:"
        echo "------------------------"
        find upload -name "*.log" -o -name "*.bak" -o -name "*.old" -o -name ".git" -o -name ".env"
        echo
        
        echo "4. Web Server Security Headers:"
        echo "------------------------------"
        if [[ -f "upload/.htaccess" ]]; then
            echo ".htaccess file exists and configured"
        else
            echo "WARNING: .htaccess file missing"
        fi
        echo
        
        echo "5. SSL/TLS Configuration:"
        echo "------------------------"
        if [[ -f "config/ssl/ssl_config.conf" ]]; then
            echo "SSL configuration template available"
        else
            echo "WARNING: SSL configuration not found"
        fi
        echo
        
        echo "6. Database Security:"
        echo "-------------------"
        if [[ -f "config/database.conf" ]]; then
            echo "Database configuration secured"
        else
            echo "WARNING: Database configuration not secured"
        fi
        echo
        
        echo "Security Audit Completed"
        echo "========================"
        
    } > "$audit_report"
    
    log "✅ Security audit completed: $audit_report"
}

# ================================================
# MAIN EXECUTION
# ================================================

main() {
    log "🚀 Starting MesChain-Sync Security Hardening..."
    
    # Check prerequisites
    check_root
    detect_environment
    
    # Create necessary directories
    mkdir -p config logs backups
    
    # Execute security hardening steps
    harden_file_permissions
    secure_file_cleanup
    configure_web_server_security
    secure_database_configuration
    configure_ssl_security
    configure_firewall
    setup_security_monitoring
    setup_secure_backup
    run_security_audit
    
    log "🎉 Security hardening completed successfully!"
    log "📋 Next steps:"
    log "   1. Review the security audit report"
    log "   2. Configure SSL certificates"
    log "   3. Set up database credentials"
    log "   4. Configure firewall rules"
    log "   5. Test all security measures"
    
    warn "Remember to:"
    warn "   - Change default passwords"
    warn "   - Update SSL certificate paths"
    warn "   - Configure monitoring email addresses"
    warn "   - Test backup and recovery procedures"
}

# Execute main function
main "$@"