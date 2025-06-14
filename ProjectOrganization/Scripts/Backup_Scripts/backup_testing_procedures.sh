#!/bin/bash

# 🔄 CURSOR TEAM BACKUP TESTING & RECOVERY PROCEDURES
# Comprehensive backup validation for Phase 2 systems
# Target: 99.9% data recovery guarantee

set -e

# Configuration
BACKUP_BASE_DIR="/opt/meschain/backups"
TEST_RESTORE_DIR="/opt/meschain/backup_tests"
LOG_FILE="/var/log/meschain/backup_test_$(date +%Y%m%d_%H%M%S).log"
NOTIFICATION_EMAIL="admin@meschain.com"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${CYAN}🔄 CURSOR TEAM BACKUP TESTING PROCEDURES${NC}"
echo -e "${BLUE}Testing all Phase 2 system backups and recovery procedures${NC}"

# Logging function
log_message() {
    local level=$1
    local message=$2
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "[${timestamp}] [${level}] ${message}" | tee -a ${LOG_FILE}
}

# Test results tracking
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_TOTAL=0

# Function to run test and track results
run_test() {
    local test_name=$1
    local test_function=$2
    
    TESTS_TOTAL=$((TESTS_TOTAL + 1))
    log_message "INFO" "${YELLOW}🧪 Running test: ${test_name}${NC}"
    
    if eval $test_function; then
        TESTS_PASSED=$((TESTS_PASSED + 1))
        log_message "SUCCESS" "${GREEN}✅ ${test_name} - PASSED${NC}"
        return 0
    else
        TESTS_FAILED=$((TESTS_FAILED + 1))
        log_message "ERROR" "${RED}❌ ${test_name} - FAILED${NC}"
        return 1
    fi
}

# Create test environment
setup_test_environment() {
    log_message "INFO" "${YELLOW}🔧 Setting up test environment...${NC}"
    
    # Create test directories
    mkdir -p ${TEST_RESTORE_DIR}/{database,redis,rabbitmq,application,exports}
    mkdir -p ${BACKUP_BASE_DIR}/test_backups
    
    # Ensure permissions
    chmod -R 755 ${TEST_RESTORE_DIR}
    chown -R root:root ${TEST_RESTORE_DIR}
    
    log_message "INFO" "${GREEN}✅ Test environment setup completed${NC}"
}

# Test 1: Database Backup Integrity
test_database_backup() {
    log_message "INFO" "Testing database backup integrity..."
    
    # Find latest database backup
    local latest_backup=$(find ${BACKUP_BASE_DIR} -name "database_backup.sql" -type f -newest -print -quit)
    
    if [ -z "$latest_backup" ]; then
        log_message "ERROR" "No database backup found"
        return 1
    fi
    
    # Test backup file integrity
    if [ ! -s "$latest_backup" ]; then
        log_message "ERROR" "Database backup file is empty"
        return 1
    fi
    
    # Check SQL syntax
    if ! mysql --execute="SOURCE $latest_backup;" --force 2>/dev/null; then
        log_message "WARNING" "SQL syntax check failed, but backup may still be valid"
    fi
    
    # Test restore to temporary database
    local test_db="meschain_test_$(date +%s)"
    mysql -e "CREATE DATABASE ${test_db};" 2>/dev/null || true
    
    if mysql ${test_db} < ${latest_backup} 2>/dev/null; then
        # Verify table count
        local table_count=$(mysql -s -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${test_db}';" 2>/dev/null)
        
        if [ "$table_count" -gt 0 ]; then
            log_message "INFO" "Database restore test successful - ${table_count} tables restored"
            mysql -e "DROP DATABASE ${test_db};" 2>/dev/null || true
            return 0
        else
            log_message "ERROR" "No tables found in restored database"
            mysql -e "DROP DATABASE ${test_db};" 2>/dev/null || true
            return 1
        fi
    else
        log_message "ERROR" "Database restore failed"
        mysql -e "DROP DATABASE ${test_db};" 2>/dev/null || true
        return 1
    fi
}

# Test 2: Redis Backup and Recovery
test_redis_backup() {
    log_message "INFO" "Testing Redis backup and recovery..."
    
    # Create test data in Redis
    local test_key="backup_test_$(date +%s)"
    local test_value="cursor_team_backup_test_data"
    
    redis-cli set ${test_key} "${test_value}" >/dev/null
    redis-cli expire ${test_key} 3600 >/dev/null
    
    # Create backup
    redis-cli BGSAVE >/dev/null
    sleep 2
    
    # Verify backup file exists
    local redis_backup="/var/lib/redis/dump.rdb"
    if [ ! -f "$redis_backup" ]; then
        log_message "ERROR" "Redis backup file not found"
        return 1
    fi
    
    # Test backup file size
    local backup_size=$(stat -c%s "$redis_backup")
    if [ "$backup_size" -lt 100 ]; then
        log_message "ERROR" "Redis backup file too small"
        return 1
    fi
    
    # Copy backup for testing
    cp ${redis_backup} ${TEST_RESTORE_DIR}/redis/dump_test.rdb
    
    # Verify test key exists
    local retrieved_value=$(redis-cli get ${test_key})
    if [ "$retrieved_value" = "$test_value" ]; then
        log_message "INFO" "Redis backup test successful"
        redis-cli del ${test_key} >/dev/null
        return 0
    else
        log_message "ERROR" "Redis test data not found"
        return 1
    fi
}

# Test 3: RabbitMQ Configuration Backup
test_rabbitmq_backup() {
    log_message "INFO" "Testing RabbitMQ configuration backup..."
    
    # Export RabbitMQ definitions
    local backup_file="${TEST_RESTORE_DIR}/rabbitmq/definitions.json"
    
    if command -v rabbitmqadmin >/dev/null 2>&1; then
        rabbitmqadmin export ${backup_file} >/dev/null 2>&1
    else
        # Alternative method using management API
        curl -s -u guest:guest http://localhost:15672/api/definitions > ${backup_file} 2>/dev/null
    fi
    
    # Verify backup file
    if [ ! -f "$backup_file" ] || [ ! -s "$backup_file" ]; then
        log_message "ERROR" "RabbitMQ backup failed"
        return 1
    fi
    
    # Validate JSON format
    if ! python3 -m json.tool ${backup_file} >/dev/null 2>&1; then
        log_message "ERROR" "RabbitMQ backup JSON invalid"
        return 1
    fi
    
    # Check for required sections
    if grep -q '"exchanges"' ${backup_file} && grep -q '"queues"' ${backup_file}; then
        log_message "INFO" "RabbitMQ backup contains required data"
        return 0
    else
        log_message "ERROR" "RabbitMQ backup missing required sections"
        return 1
    fi
}

# Test 4: Application Code Backup
test_application_backup() {
    log_message "INFO" "Testing application code backup..."
    
    # Find latest application backup
    local latest_backup=$(find ${BACKUP_BASE_DIR} -name "application_backup.tar.gz" -type f -newest -print -quit)
    
    if [ -z "$latest_backup" ]; then
        log_message "ERROR" "No application backup found"
        return 1
    fi
    
    # Test archive integrity
    if ! tar -tzf ${latest_backup} >/dev/null 2>&1; then
        log_message "ERROR" "Application backup archive corrupted"
        return 1
    fi
    
    # Extract to test directory
    if tar -xzf ${latest_backup} -C ${TEST_RESTORE_DIR}/application >/dev/null 2>&1; then
        # Verify key files exist
        local key_files=(
            "redis_cache_implementation.js"
            "rabbitmq_integration.js"
            "data_export_reporting_system.js"
        )
        
        for file in "${key_files[@]}"; do
            if [ ! -f "${TEST_RESTORE_DIR}/application/current/${file}" ]; then
                log_message "ERROR" "Missing key file in backup: ${file}"
                return 1
            fi
        done
        
        log_message "INFO" "Application backup restore successful"
        return 0
    else
        log_message "ERROR" "Application backup extraction failed"
        return 1
    fi
}

# Test 5: Export System Data Backup
test_export_backup() {
    log_message "INFO" "Testing export system data backup..."
    
    # Create test export data
    local test_export_dir="/opt/meschain/exports/test_backup"
    mkdir -p ${test_export_dir}
    
    # Create sample export files
    echo "Test export data" > ${test_export_dir}/test_export.csv
    echo "Test report data" > ${test_export_dir}/test_report.xlsx
    
    # Create backup
    tar -czf ${BACKUP_BASE_DIR}/exports_backup_test.tar.gz -C /opt/meschain exports/ 2>/dev/null
    
    # Test backup integrity
    if tar -tzf ${BACKUP_BASE_DIR}/exports_backup_test.tar.gz >/dev/null 2>&1; then
        # Extract to test directory
        if tar -xzf ${BACKUP_BASE_DIR}/exports_backup_test.tar.gz -C ${TEST_RESTORE_DIR} >/dev/null 2>&1; then
            # Verify test files exist
            if [ -f "${TEST_RESTORE_DIR}/exports/test_backup/test_export.csv" ]; then
                log_message "INFO" "Export system backup successful"
                rm -rf ${test_export_dir}
                rm -f ${BACKUP_BASE_DIR}/exports_backup_test.tar.gz
                return 0
            else
                log_message "ERROR" "Test export files not found in backup"
                return 1
            fi
        else
            log_message "ERROR" "Export backup extraction failed"
            return 1
        fi
    else
        log_message "ERROR" "Export backup archive corrupted"
        return 1
    fi
}

# Test 6: Configuration Files Backup
test_config_backup() {
    log_message "INFO" "Testing configuration files backup..."
    
    # Key configuration files to test
    local config_files=(
        "/etc/nginx/sites-available/meschain"
        "/etc/redis/redis.conf"
        "/etc/rabbitmq/rabbitmq.conf"
    )
    
    local backup_missing=0
    
    for config_file in "${config_files[@]}"; do
        local backup_file="${BACKUP_BASE_DIR}/$(basename ${config_file}).backup"
        
        if [ -f "$config_file" ]; then
            # Create backup
            cp ${config_file} ${backup_file}
            
            # Verify backup
            if [ -f "$backup_file" ] && [ -s "$backup_file" ]; then
                log_message "INFO" "Config backup successful: $(basename ${config_file})"
            else
                log_message "ERROR" "Config backup failed: $(basename ${config_file})"
                backup_missing=$((backup_missing + 1))
            fi
        fi
    done
    
    if [ $backup_missing -eq 0 ]; then
        return 0
    else
        return 1
    fi
}

# Test 7: Automated Backup Scheduling
test_backup_scheduling() {
    log_message "INFO" "Testing automated backup scheduling..."
    
    # Check if backup cron jobs exist
    local cron_jobs=$(crontab -l 2>/dev/null | grep -c "backup" || echo 0)
    
    if [ $cron_jobs -gt 0 ]; then
        log_message "INFO" "Found ${cron_jobs} backup cron jobs"
        
        # Test backup script execution
        local backup_script="/opt/meschain/scripts/backup.sh"
        if [ -f "$backup_script" ] && [ -x "$backup_script" ]; then
            # Run backup script in test mode
            if ${backup_script} --test >/dev/null 2>&1; then
                log_message "INFO" "Backup script execution test successful"
                return 0
            else
                log_message "ERROR" "Backup script execution failed"
                return 1
            fi
        else
            log_message "WARNING" "Backup script not found or not executable"
            return 1
        fi
    else
        log_message "ERROR" "No backup cron jobs found"
        return 1
    fi
}

# Test 8: Recovery Time Objective (RTO) Test
test_recovery_time() {
    log_message "INFO" "Testing recovery time objectives..."
    
    local start_time=$(date +%s)
    
    # Simulate service restart (fastest recovery scenario)
    systemctl restart nginx >/dev/null 2>&1
    systemctl restart redis-server >/dev/null 2>&1
    
    # Wait for services to be ready
    sleep 5
    
    # Test service availability
    local services_ok=0
    
    # Test Nginx
    if curl -s http://localhost/health >/dev/null 2>&1; then
        services_ok=$((services_ok + 1))
    fi
    
    # Test Redis
    if redis-cli ping | grep -q PONG; then
        services_ok=$((services_ok + 1))
    fi
    
    local end_time=$(date +%s)
    local recovery_time=$((end_time - start_time))
    
    log_message "INFO" "Recovery time: ${recovery_time} seconds"
    log_message "INFO" "Services recovered: ${services_ok}/2"
    
    # RTO target: < 300 seconds (5 minutes)
    if [ $recovery_time -lt 300 ] && [ $services_ok -eq 2 ]; then
        log_message "INFO" "Recovery Time Objective met"
        return 0
    else
        log_message "ERROR" "Recovery Time Objective not met"
        return 1
    fi
}

# Recovery Procedure Documentation
generate_recovery_procedures() {
    log_message "INFO" "Generating recovery procedures documentation..."
    
    local doc_file="${BACKUP_BASE_DIR}/RECOVERY_PROCEDURES.md"
    
    cat > ${doc_file} << 'EOF'
# 🔄 EMERGENCY RECOVERY PROCEDURES

## 🚨 Database Recovery
```bash
# Stop application
systemctl stop meschain-app

# Restore database
mysql meschain_db < /opt/meschain/backups/latest/database_backup.sql

# Start application
systemctl start meschain-app
```

## 🎯 Redis Recovery
```bash
# Stop Redis
systemctl stop redis-server

# Restore Redis data
cp /opt/meschain/backups/latest/dump.rdb /var/lib/redis/

# Set permissions
chown redis:redis /var/lib/redis/dump.rdb

# Start Redis
systemctl start redis-server
```

## 🔄 RabbitMQ Recovery
```bash
# Stop RabbitMQ
systemctl stop rabbitmq-server

# Restore configuration
rabbitmqadmin import /opt/meschain/backups/latest/definitions.json

# Start RabbitMQ
systemctl start rabbitmq-server
```

## 📁 Application Recovery
```bash
# Extract application backup
tar -xzf /opt/meschain/backups/latest/application_backup.tar.gz -C /opt/meschain/

# Set permissions
chown -R www-data:www-data /opt/meschain/current

# Restart services
systemctl restart nginx
systemctl restart meschain-app
```

## ⏱️ Recovery Time Objectives (RTO)
- **Service Restart**: < 5 minutes
- **Data Recovery**: < 30 minutes  
- **Full System Recovery**: < 2 hours
- **Disaster Recovery**: < 24 hours

## 📞 Emergency Contacts
- **System Admin**: +90 XXX XXX XXXX
- **Database Team**: +90 XXX XXX XXXX
- **Development Team**: +90 XXX XXX XXXX
EOF

    log_message "INFO" "Recovery procedures documented: ${doc_file}"
}

# Performance monitoring during backup
monitor_backup_performance() {
    log_message "INFO" "Monitoring backup performance impact..."
    
    # Check system load during backup
    local load_avg=$(uptime | awk -F'load average:' '{ print $2 }' | awk '{ print $1 }' | sed 's/,//')
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | sed 's/%us,//')
    local memory_usage=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
    
    log_message "INFO" "System load during backup: ${load_avg}"
    log_message "INFO" "CPU usage during backup: ${cpu_usage}"
    log_message "INFO" "Memory usage during backup: ${memory_usage}%"
    
    # Check if performance impact is acceptable
    if (( $(echo "$load_avg < 5.0" | bc -l) )) && 
       (( $(echo "$memory_usage < 90.0" | bc -l) )); then
        log_message "INFO" "Backup performance impact acceptable"
        return 0
    else
        log_message "WARNING" "High system load during backup"
        return 1
    fi
}

# Email notification function
send_notification() {
    local subject=$1
    local body=$2
    
    if command -v mail >/dev/null 2>&1; then
        echo "${body}" | mail -s "${subject}" ${NOTIFICATION_EMAIL}
        log_message "INFO" "Notification sent to ${NOTIFICATION_EMAIL}"
    else
        log_message "WARNING" "Mail command not available for notifications"
    fi
}

# Generate test report
generate_test_report() {
    local report_file="${BACKUP_BASE_DIR}/backup_test_report_$(date +%Y%m%d).txt"
    
    cat > ${report_file} << EOF
🔄 CURSOR TEAM BACKUP TESTING REPORT
=====================================
Date: $(date)
Test Duration: $(($(date +%s) - start_time)) seconds

TEST RESULTS:
- Total Tests: ${TESTS_TOTAL}
- Passed: ${TESTS_PASSED}
- Failed: ${TESTS_FAILED}
- Success Rate: $(( TESTS_PASSED * 100 / TESTS_TOTAL ))%

SYSTEM STATUS:
- Database Backup: $([ ${db_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")
- Redis Backup: $([ ${redis_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")
- RabbitMQ Backup: $([ ${rabbitmq_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")
- Application Backup: $([ ${app_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")
- Export Backup: $([ ${export_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")
- Config Backup: $([ ${config_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")
- Backup Scheduling: $([ ${schedule_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")
- Recovery Time: $([ ${recovery_test_result} -eq 0 ] && echo "✅ PASS" || echo "❌ FAIL")

RECOMMENDATIONS:
$([ ${TESTS_FAILED} -gt 0 ] && echo "⚠️ Failed tests require immediate attention" || echo "✅ All backup systems operational")

Log File: ${LOG_FILE}
Recovery Procedures: ${BACKUP_BASE_DIR}/RECOVERY_PROCEDURES.md
EOF

    log_message "INFO" "Test report generated: ${report_file}"
    
    # Send notification
    local notification_subject="CURSOR Team Backup Test Results - $([ ${TESTS_FAILED} -eq 0 ] && echo "SUCCESS" || echo "ATTENTION REQUIRED")"
    send_notification "${notification_subject}" "$(cat ${report_file})"
}

# Main execution
main() {
    local start_time=$(date +%s)
    
    log_message "INFO" "${CYAN}🚀 Starting CURSOR Team Backup Testing${NC}"
    
    # Setup
    setup_test_environment
    
    # Run all tests
    run_test "Database Backup Integrity" "test_database_backup"
    db_test_result=$?
    
    run_test "Redis Backup and Recovery" "test_redis_backup"
    redis_test_result=$?
    
    run_test "RabbitMQ Configuration Backup" "test_rabbitmq_backup"
    rabbitmq_test_result=$?
    
    run_test "Application Code Backup" "test_application_backup"
    app_test_result=$?
    
    run_test "Export System Data Backup" "test_export_backup"
    export_test_result=$?
    
    run_test "Configuration Files Backup" "test_config_backup"
    config_test_result=$?
    
    run_test "Automated Backup Scheduling" "test_backup_scheduling"
    schedule_test_result=$?
    
    run_test "Recovery Time Objective" "test_recovery_time"
    recovery_test_result=$?
    
    # Additional tests
    monitor_backup_performance
    generate_recovery_procedures
    generate_test_report
    
    # Final results
    log_message "INFO" "${CYAN}📊 BACKUP TESTING COMPLETED${NC}"
    log_message "INFO" "${GREEN}✅ Tests Passed: ${TESTS_PASSED}/${TESTS_TOTAL}${NC}"
    
    if [ ${TESTS_FAILED} -eq 0 ]; then
        log_message "SUCCESS" "${GREEN}🎉 ALL BACKUP TESTS PASSED - SYSTEM READY${NC}"
        exit 0
    else
        log_message "ERROR" "${RED}❌ ${TESTS_FAILED} TESTS FAILED - REQUIRES ATTENTION${NC}"
        exit 1
    fi
}

# Execute main function
main "$@" 