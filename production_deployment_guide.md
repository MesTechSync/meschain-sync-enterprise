# 🚀 MesChain-Sync Production Deployment Guide

**Version:** 3.0.4.0  
**Author:** Musti - DevOps & Infrastructure Team  
**Date:** 2025-01-05  

---

## 📋 **OVERVIEW**

Bu guide, MesChain-Sync sisteminin production ortamına güvenli ve başarılı bir şekilde deploy edilmesi için gereken tüm adımları detaylandırır.

---

## 🎯 **PRE-DEPLOYMENT CHECKLIST**

### ✅ **System Requirements**
- [ ] **PHP:** 7.4+ (Recommended: 8.0+)
- [ ] **MySQL:** 5.7+ or MariaDB 10.3+
- [ ] **Web Server:** Apache 2.4+ or Nginx 1.18+
- [ ] **Memory:** Minimum 2GB RAM (Recommended: 4GB+)
- [ ] **Storage:** Minimum 10GB free space
- [ ] **SSL Certificate:** Valid SSL certificate configured

### ✅ **Security Requirements**
- [ ] Server hardening completed
- [ ] Firewall configured
- [ ] SSH key-based authentication
- [ ] Strong passwords for all accounts
- [ ] Database user with limited privileges
- [ ] Regular backup system in place

### ✅ **Dependencies**
- [ ] OpenSSL for encryption
- [ ] Curl for API communications
- [ ] GD extension for image processing
- [ ] JSON extension for data handling
- [ ] MySQLi or PDO extension
- [ ] ZIP extension for file handling

---

## 🗂️ **DEPLOYMENT STEPS**

### **Step 1: Environment Preparation**

#### 1.1 Create Deployment Directory
```bash
sudo mkdir -p /var/www/meschain-sync
sudo chown $USER:$USER /var/www/meschain-sync
cd /var/www/meschain-sync
```

#### 1.2 Download and Extract Files
```bash
# Upload your project files here
# Or use git clone if version controlled
```

#### 1.3 Set Up Configuration
```bash
# Copy configuration templates
cp config/database.conf.example config/database.conf
cp config/environment.conf.example config/environment.conf

# Edit configurations
nano config/database.conf
nano config/environment.conf
```

### **Step 2: Database Setup**

#### 2.1 Create Database and User
```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE meschain_sync CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Create dedicated user
CREATE USER 'meschain_app'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- Grant necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON meschain_sync.* TO 'meschain_app'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

#### 2.2 Run Database Migration
```bash
# Run the migration script
mysql -u meschain_app -p meschain_sync < database_migration.sql

# Verify tables were created
mysql -u meschain_app -p meschain_sync -e "SHOW TABLES LIKE 'oc_meschain_%';"
```

### **Step 3: Security Hardening**

#### 3.1 Run Security Hardening Script
```bash
# Make script executable
chmod +x security_hardening.sh

# Run security hardening
./security_hardening.sh
```

#### 3.2 Verify Security Settings
```bash
# Check file permissions
ls -la upload/config.php
ls -la upload/admin/config.php

# Verify .htaccess is in place
ls -la upload/.htaccess

# Test security headers
curl -I https://yourdomain.com
```

### **Step 4: Web Server Configuration**

#### 4.1 Apache Configuration
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/meschain-sync/upload
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt
    
    # Security headers
    Include /var/www/meschain-sync/config/ssl/ssl_config.conf
    
    # Directory settings
    <Directory /var/www/meschain-sync/upload>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/meschain_error.log
    CustomLog ${APACHE_LOG_DIR}/meschain_access.log combined
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

#### 4.2 Nginx Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/meschain-sync/upload;
    index index.php index.html index.htm;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    include /var/www/meschain-sync/config/ssl/ssl_config.conf;
    
    # Security headers
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security restrictions
    location ~ /\. {
        deny all;
    }
    
    location ~* \.(bak|config|sql|fla|psd|ini|log|sh|inc|swp|dist)$ {
        deny all;
    }
    
    # Logging
    access_log /var/log/nginx/meschain_access.log;
    error_log /var/log/nginx/meschain_error.log;
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

### **Step 5: Application Configuration**

#### 5.1 OpenCart Configuration
```bash
# Navigate to upload directory
cd upload

# Create config files from templates
cp config-dist.php config.php
cp admin/config-dist.php admin/config.php

# Edit configuration files
nano config.php
nano admin/config.php
```

#### 5.2 Sample config.php
```php
<?php
// HTTP
define('HTTP_SERVER', 'https://yourdomain.com/');

// HTTPS
define('HTTPS_SERVER', 'https://yourdomain.com/');

// DIR
define('DIR_APPLICATION', '/var/www/meschain-sync/upload/catalog/');
define('DIR_SYSTEM', '/var/www/meschain-sync/upload/system/');
define('DIR_IMAGE', '/var/www/meschain-sync/upload/image/');
define('DIR_STORAGE', '/var/www/meschain-sync/upload/system/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'meschain_app');
define('DB_PASSWORD', 'your_secure_password_here');
define('DB_DATABASE', 'meschain_sync');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
?>
```

### **Step 6: System Verification**

#### 6.1 Run System Health Check
```bash
# Execute the DevOps automation script
php devops_automation.php --operation=health_check
```

#### 6.2 Test Database Connection
```bash
# Test database connectivity
php -r "
$mysqli = new mysqli('localhost', 'meschain_app', 'password', 'meschain_sync');
if (\$mysqli->connect_error) {
    die('Connection failed: ' . \$mysqli->connect_error);
}
echo 'Database connection successful!' . PHP_EOL;
\$mysqli->close();
"
```

#### 6.3 Verify Web Access
```bash
# Test web server response
curl -I https://yourdomain.com

# Test admin panel access
curl -I https://yourdomain.com/admin

# Check for proper redirects
curl -I http://yourdomain.com
```

### **Step 7: Monitoring Setup**

#### 7.1 Start Monitoring Dashboard
```bash
# Copy monitoring dashboard
cp monitoring_dashboard.html /var/www/meschain-sync/monitoring/

# Set up monitoring endpoint
echo "alias monitor='php devops_automation.php --operation=system_status'" >> ~/.bashrc
source ~/.bashrc
```

#### 7.2 Configure Log Monitoring
```bash
# Start log monitoring
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Configure log rotation
sudo cp config/monitoring/logrotate.conf /etc/logrotate.d/meschain
```

### **Step 8: Backup Configuration**

#### 8.1 Set Up Automated Backups
```bash
# Make backup script executable
chmod +x config/secure_backup.sh

# Add to crontab
(crontab -l 2>/dev/null; echo "0 2 * * * /var/www/meschain-sync/config/secure_backup.sh") | crontab -

# Verify cron job
crontab -l
```

#### 8.2 Test Backup System
```bash
# Run manual backup test
./config/secure_backup.sh

# Verify backup files
ls -la /var/backups/meschain/
```

---

## 🧪 **POST-DEPLOYMENT TESTING**

### **Functional Tests**

#### Test 1: Core System Functionality
```bash
# Test admin login
curl -X POST https://yourdomain.com/admin/index.php \
  -d "username=admin&password=your_password"

# Test marketplace connections
php devops_automation.php --operation=test_marketplaces
```

#### Test 2: Security Tests
```bash
# Test security headers
curl -I https://yourdomain.com | grep -E "(X-Frame-Options|X-XSS-Protection|X-Content-Type-Options)"

# Test SQL injection protection
curl "https://yourdomain.com/?id=1' OR '1'='1"

# Test directory access
curl https://yourdomain.com/.git/
curl https://yourdomain.com/config.php
```

#### Test 3: Performance Tests
```bash
# Test page load times
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com

# Test database performance
php devops_automation.php --operation=performance_test
```

### **Load Testing**
```bash
# Install Apache Bench (if not installed)
sudo apt-get install apache2-utils

# Run load test
ab -n 1000 -c 10 https://yourdomain.com/

# Monitor system resources during test
htop
```

---

## 📊 **MONITORING & MAINTENANCE**

### **Daily Monitoring Tasks**
- [ ] Check system health dashboard
- [ ] Review error logs
- [ ] Verify backup completion
- [ ] Monitor disk space usage
- [ ] Check security alerts

### **Weekly Maintenance**
- [ ] Review performance metrics
- [ ] Update security rules
- [ ] Clean old log files
- [ ] Test backup restoration
- [ ] Review access logs

### **Monthly Maintenance**
- [ ] Security audit
- [ ] Database optimization
- [ ] Update system packages
- [ ] Review and update documentation
- [ ] Performance optimization

---

## 🚨 **TROUBLESHOOTING**

### **Common Issues**

#### Issue 1: Database Connection Errors
```bash
# Check MySQL service
sudo systemctl status mysql

# Test connection
mysql -u meschain_app -p meschain_sync

# Check permissions
mysql -u root -p -e "SHOW GRANTS FOR 'meschain_app'@'localhost';"
```

#### Issue 2: Permission Errors
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/meschain-sync/upload
sudo chmod -R 755 /var/www/meschain-sync/upload
sudo chmod 600 /var/www/meschain-sync/upload/config.php
```

#### Issue 3: SSL Certificate Issues
```bash
# Check certificate validity
openssl x509 -in /path/to/certificate.crt -text -noout

# Test SSL configuration
openssl s_client -connect yourdomain.com:443
```

#### Issue 4: Performance Issues
```bash
# Check system resources
htop
df -h
iostat

# Optimize database
mysql -u root -p -e "OPTIMIZE TABLE meschain_sync.oc_meschain_marketplace_logs;"

# Clear cache
rm -rf /var/www/meschain-sync/upload/system/storage/cache/*
```

---

## 📞 **SUPPORT & ESCALATION**

### **Emergency Contacts**
- **DevOps Team:** devops@company.com
- **Security Team:** security@company.com
- **Development Team:** dev@company.com

### **Escalation Procedure**
1. **Level 1:** Check monitoring dashboard and logs
2. **Level 2:** Run diagnostic scripts
3. **Level 3:** Contact DevOps team
4. **Level 4:** Emergency rollback procedure

### **Emergency Rollback**
```bash
# Backup current state
cp -r /var/www/meschain-sync /var/backups/emergency_backup_$(date +%Y%m%d_%H%M%S)

# Restore from last known good backup
tar -xzf /var/backups/meschain/files_backup_YYYYMMDD_HHMMSS.tar.gz -C /var/www/
mysql -u meschain_app -p meschain_sync < /var/backups/meschain/db_backup_YYYYMMDD_HHMMSS.sql

# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

---

## ✅ **DEPLOYMENT COMPLETION CHECKLIST**

### **Pre-Go-Live**
- [ ] All security measures implemented
- [ ] SSL certificate configured and tested
- [ ] Database migration completed
- [ ] Backup system operational
- [ ] Monitoring dashboard active
- [ ] Performance tests passed
- [ ] Security tests passed
- [ ] Documentation updated

### **Go-Live**
- [ ] DNS records updated
- [ ] Traffic routing verified
- [ ] Real-time monitoring active
- [ ] Alert systems enabled
- [ ] Support team notified
- [ ] Rollback plan ready

### **Post-Go-Live**
- [ ] System stability verified (24 hours)
- [ ] Performance metrics within acceptable range
- [ ] No critical errors in logs
- [ ] Backup verification completed
- [ ] User acceptance testing passed
- [ ] Final sign-off obtained

---

## 📚 **ADDITIONAL RESOURCES**

- **System Architecture:** `/docs/architecture.md`
- **API Documentation:** `/docs/api.md`
- **Security Guide:** `/docs/security.md`
- **Monitoring Guide:** `/docs/monitoring.md`
- **Troubleshooting:** `/docs/troubleshooting.md`

---

**🎉 Deployment Guide Completed!**

*This guide should be followed step-by-step for a successful production deployment. Always test in a staging environment first before deploying to production.* 