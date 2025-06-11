# 👨‍🎓 CURSOR TEAM - User Training Guide

## 🎯 **Phase 2 Systems Training Program**
**Target Audience**: System Administrators, Dashboard Users  
**Duration**: 2 days intensive training  
**Completion**: 7 days from deployment  

---

## 📚 **Training Module 1: Super Admin Dashboard (100% Complete)**

### **🔥 New Features Overview**

#### **1. Data Export Controls (NEW)**
- **Excel Export**: Click export button → Select date range → Download
- **CSV Export**: Bulk data export for analysis
- **PDF Reports**: Formatted executive summaries
- **Progress Tracking**: Real-time download status

**Practice Exercise**:
```
1. Navigate to Dashboard → Export section
2. Select "Last 30 Days" date range
3. Choose Excel format
4. Click "Generate Export"
5. Monitor progress bar
6. Download completed file
```

#### **2. Automated Reporting System (NEW)**
- **Schedule Management**: Set daily/weekly/monthly reports
- **Email Recipients**: Configure distribution lists
- **Report Templates**: Choose report formats
- **History View**: Access previous reports

**Practice Exercise**:
```
1. Go to Reports → Schedule Management
2. Create new weekly report
3. Add email recipients
4. Set schedule for Monday 9:00 AM
5. Select template: "Executive Summary"
6. Save and activate
```

#### **3. System Configuration Panel (NEW)**
- **Cache Settings**: Redis performance tuning
- **Queue Management**: RabbitMQ monitoring
- **Performance Thresholds**: Alert configuration
- **Health Monitoring**: System status tracking

---

## 🎯 **Training Module 2: Redis Cache System**

### **Understanding Cache Performance**

#### **Key Metrics to Monitor**:
- **Hit Rate**: Target >85% (Currently: 87.3%)
- **Response Time**: Target <5ms
- **Memory Usage**: Monitor for optimization
- **Connection Count**: Track active connections

#### **Cache Management Tasks**:
```bash
# Check Redis status
redis-cli ping

# Monitor cache stats
redis-cli info stats

# View memory usage
redis-cli info memory

# Clear specific cache keys
redis-cli del "meschain:product:123"

# Flush all cache (emergency only)
redis-cli flushall
```

#### **Troubleshooting Common Issues**:
1. **Low Hit Rate (<80%)**:
   - Check TTL settings
   - Review cache warming process
   - Analyze access patterns

2. **High Memory Usage**:
   - Review maxmemory settings
   - Check LRU eviction policy
   - Monitor key expiration

3. **Connection Issues**:
   - Verify Redis service status
   - Check network connectivity
   - Review connection pooling

---

## 🔄 **Training Module 3: RabbitMQ Message System**

### **Queue Management Overview**

#### **Key Queues to Monitor**:
- **orders.new**: Order processing (Priority: High)
- **notifications.email**: Email notifications
- **notifications.sms**: SMS notifications  
- **products.sync.***: Marketplace synchronization
- **analytics.events**: Analytics data

#### **Management Tasks**:
```bash
# Check RabbitMQ status
rabbitmqctl status

# List all queues
rabbitmqctl list_queues

# Monitor queue messages
rabbitmqctl list_queues name messages consumers

# Purge queue (emergency)
rabbitmqctl purge_queue orders.new

# Access management UI
# http://server:15672 (meschain/password)
```

#### **Performance Monitoring**:
- **Message Rate**: Target >10,000 msg/sec
- **Queue Length**: Should be minimal (<100)
- **Consumer Count**: Monitor active consumers
- **Memory Usage**: Track RabbitMQ memory

---

## 📊 **Training Module 4: Export & Reporting System**

### **Report Generation Workflow**

#### **Manual Export Process**:
1. **Data Selection**:
   - Choose data type (Orders, Products, Customers)
   - Set date range
   - Apply filters

2. **Format Selection**:
   - Excel: Full formatting, multiple sheets
   - CSV: Raw data, easy import
   - PDF: Executive presentation

3. **Generation & Download**:
   - Monitor progress bar
   - Receive email notification
   - Download from reports section

#### **Automated Reports Setup**:
```
Daily Reports (06:00):
- Sales summary
- System performance
- Error logs

Weekly Reports (Monday 07:00):
- Comprehensive analytics
- Performance trends
- User activity

Monthly Reports (1st 08:00):
- Executive dashboard
- Financial summary
- Growth metrics
```

#### **Email Configuration**:
```bash
# Configure SMTP settings
EMAIL_SERVICE=gmail
EMAIL_USER=reports@meschain.com
EMAIL_PASS=secure_app_password

# Distribution lists
DAILY_REPORTS=admin@meschain.com,manager@meschain.com
WEEKLY_REPORTS=team@meschain.com
MONTHLY_REPORTS=executives@meschain.com
```

---

## 🔧 **Training Module 5: Performance Monitoring**

### **Monitoring Dashboard Navigation**

#### **Real-time Metrics**:
- **System Health**: CPU, Memory, Disk usage
- **Cache Performance**: Hit rates, response times
- **Queue Status**: Message throughput, queue lengths
- **Database**: Query performance, connection pool
- **Export System**: Generation rates, success rates

#### **Alert Configuration**:
```
Warning Thresholds:
- CPU Usage > 80%
- Memory Usage > 85%
- Cache Hit Rate < 80%
- Database Response > 50ms
- Queue Length > 100

Critical Thresholds:
- CPU Usage > 95%
- Memory Usage > 95%
- Cache Hit Rate < 70%
- Database Response > 100ms
- System Downtime > 1 minute
```

#### **Performance Tuning**:
1. **Cache Optimization**:
   - Adjust TTL values
   - Configure cache warming
   - Monitor key patterns

2. **Queue Optimization**:
   - Scale consumer count
   - Adjust prefetch settings
   - Monitor message patterns

3. **Database Optimization**:
   - Review slow queries
   - Check connection pool
   - Monitor index usage

---

## 🚨 **Training Module 6: Troubleshooting & Emergency Procedures**

### **Common Issues & Solutions**

#### **Dashboard Loading Issues**:
```bash
# Check Nginx status
systemctl status nginx

# Restart Nginx
systemctl restart nginx

# Check application logs
tail -f /var/log/meschain/application.log
```

#### **Cache Performance Issues**:
```bash
# Check Redis memory
redis-cli info memory

# Monitor slow queries
redis-cli --latency

# Check connection count
redis-cli info clients
```

#### **Queue Backup Issues**:
```bash
# Check queue lengths
rabbitmqctl list_queues name messages

# Increase consumer count
# (Application specific - contact development team)

# Emergency queue purge
rabbitmqctl purge_queue queue_name
```

#### **Export System Issues**:
```bash
# Check export service status
systemctl status meschain-export

# Review export logs
tail -f /var/log/meschain/export.log

# Clear failed exports
rm -rf /opt/meschain/exports/failed/*
```

---

## 📋 **Training Module 7: Daily Operations Checklist**

### **Morning Checklist (09:00)**
- [ ] Check system status dashboard
- [ ] Review overnight alerts
- [ ] Verify backup completion
- [ ] Check cache hit rates
- [ ] Monitor queue lengths
- [ ] Review export generation

### **Midday Checklist (13:00)**
- [ ] Monitor performance metrics
- [ ] Check database performance
- [ ] Review user activity
- [ ] Verify scheduled reports
- [ ] Check disk space usage

### **Evening Checklist (18:00)**
- [ ] Review daily performance
- [ ] Check error logs
- [ ] Verify backup schedules
- [ ] Monitor system alerts
- [ ] Prepare daily report

### **Weekly Tasks (Monday)**
- [ ] Review weekly performance trends
- [ ] Analyze cache optimization opportunities
- [ ] Check system capacity planning
- [ ] Review alert thresholds
- [ ] Update monitoring configurations

---

## 🎓 **Training Assessment & Certification**

### **Practical Exercises**

#### **Exercise 1: Dashboard Navigation**
1. Access Super Admin Dashboard
2. Navigate to each major section
3. Generate a sample export
4. Schedule an automated report
5. Configure system alerts

#### **Exercise 2: Performance Monitoring**
1. Identify current system metrics
2. Set up performance alerts
3. Simulate a high-load scenario
4. Monitor cache performance
5. Review queue throughput

#### **Exercise 3: Troubleshooting**
1. Identify a simulated issue
2. Use monitoring tools to diagnose
3. Apply appropriate solution
4. Verify resolution
5. Document the process

### **Certification Criteria**
- ✅ Complete all 7 training modules
- ✅ Pass practical exercises (80% score)
- ✅ Demonstrate troubleshooting skills
- ✅ Successfully operate systems for 1 week

---

## 📞 **Support & Resources**

### **Emergency Contacts**
- **System Administrator**: ext. 101
- **Database Team**: ext. 102  
- **Development Team**: ext. 103
- **24/7 Support**: support@meschain.com

### **Documentation Links**
- **Redis Documentation**: [Internal Wiki/Redis]
- **RabbitMQ Guide**: [Internal Wiki/RabbitMQ]
- **Export System Manual**: [Internal Wiki/Export]
- **Monitoring Guide**: [Internal Wiki/Monitoring]

### **Training Schedule**
```
Day 1 (Morning): Modules 1-3 + Hands-on Practice
Day 1 (Afternoon): Modules 4-5 + Performance Monitoring
Day 2 (Morning): Module 6-7 + Troubleshooting Scenarios  
Day 2 (Afternoon): Assessment + Certification
```

---

**🎯 Training Completion Target**: 100% certified administrators within 7 days  
**📊 Success Metrics**: 95%+ system uptime, <30ms response times  
**🏆 Certification Level**: CURSOR Team Production Ready ⭐⭐⭐⭐⭐ 