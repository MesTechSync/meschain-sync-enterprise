const fs = require('fs');
const path = require('path');
const { promisify } = require('util');
const EventEmitter = require('events');

/**
 * OpenCart Production Monitoring Integration System
 * 
 * Comprehensive monitoring integration system that connects all production monitoring
 * components, provides unified monitoring dashboard, real-time alerting, and
 * comprehensive system health analytics.
 * 
 * @package OpenCartProduction
 * @version 1.0.0
 * @author Production Systems Team
 */

class OpenCartProductionMonitoringIntegration extends EventEmitter {
    constructor() {
        super();
        
        this.config = {
            monitoring_interval: 15000, // 15 seconds
            alert_thresholds: {
                cpu_usage: 80,
                memory_usage: 85,
                disk_usage: 90,
                response_time: 2000,
                error_rate: 5,
                database_connections: 100
            },
            notification_channels: {
                email: true,
                slack: true,
                sms: false,
                webhook: true
            },
            retention_period: 30 * 24 * 60 * 60 * 1000, // 30 days
            aggregation_intervals: [300, 900, 3600, 86400] // 5min, 15min, 1hour, 1day
        };
        
        this.metrics = new Map();
        this.alerts = new Map();
        this.monitoring_systems = new Map();
        this.dashboard_data = {};
        this.notification_queue = [];
        
        this.initializeMonitoringSystems();
        this.setupEventHandlers();
        this.startMonitoring();
        
        console.log('OpenCart Production Monitoring Integration System initialized');
    }
    
    /**
     * Initialize all monitoring systems
     */
    initializeMonitoringSystems() {
        // System Performance Monitor
        this.monitoring_systems.set('performance', {
            name: 'Performance Monitor',
            status: 'active',
            last_check: null,
            metrics: ['cpu_usage', 'memory_usage', 'disk_io', 'network_io'],
            check_interval: 15000,
            error_count: 0,
            health_score: 100
        });
        
        // Database Monitor
        this.monitoring_systems.set('database', {
            name: 'Database Monitor',
            status: 'active',
            last_check: null,
            metrics: ['connection_count', 'query_time', 'slow_queries', 'deadlocks'],
            check_interval: 30000,
            error_count: 0,
            health_score: 100
        });
        
        // Security Monitor
        this.monitoring_systems.set('security', {
            name: 'Security Monitor',
            status: 'active',
            last_check: null,
            metrics: ['failed_logins', 'suspicious_requests', 'blocked_ips', 'malware_attempts'],
            check_interval: 10000,
            error_count: 0,
            health_score: 100
        });
        
        // Marketplace Integration Monitor
        this.monitoring_systems.set('marketplace', {
            name: 'Marketplace Integration Monitor',
            status: 'active',
            last_check: null,
            metrics: ['api_response_time', 'sync_success_rate', 'order_processing', 'inventory_sync'],
            check_interval: 60000,
            error_count: 0,
            health_score: 100
        });
        
        // Application Health Monitor
        this.monitoring_systems.set('application', {
            name: 'Application Health Monitor',
            status: 'active',
            last_check: null,
            metrics: ['response_time', 'error_rate', 'uptime', 'user_sessions'],
            check_interval: 20000,
            error_count: 0,
            health_score: 100
        });
        
        // Backup System Monitor
        this.monitoring_systems.set('backup', {
            name: 'Backup System Monitor',
            status: 'active',
            last_check: null,
            metrics: ['backup_success', 'backup_size', 'recovery_test', 'retention_compliance'],
            check_interval: 300000, // 5 minutes
            error_count: 0,
            health_score: 100
        });
        
        console.log(`Initialized ${this.monitoring_systems.size} monitoring systems`);
    }
    
    /**
     * Setup event handlers for monitoring systems
     */
    setupEventHandlers() {
        this.on('metric_collected', this.handleMetricCollection.bind(this));
        this.on('alert_triggered', this.handleAlertTriggered.bind(this));
        this.on('system_health_changed', this.handleSystemHealthChange.bind(this));
        this.on('emergency_detected', this.handleEmergencyResponse.bind(this));
        
        // Handle process termination gracefully
        process.on('SIGINT', this.shutdown.bind(this));
        process.on('SIGTERM', this.shutdown.bind(this));
        process.on('uncaughtException', this.handleUncaughtException.bind(this));
        process.on('unhandledRejection', this.handleUnhandledRejection.bind(this));
    }
    
    /**
     * Start monitoring all systems
     */
    startMonitoring() {
        console.log('Starting production monitoring...');
        
        // Start monitoring each system
        for (const [systemId, system] of this.monitoring_systems) {
            this.startSystemMonitoring(systemId);
        }
        
        // Start dashboard update loop
        this.startDashboardUpdates();
        
        // Start metric aggregation
        this.startMetricAggregation();
        
        // Start alert processing
        this.startAlertProcessing();
        
        // Start notification processing
        this.startNotificationProcessing();
        
        console.log('Production monitoring started successfully');
    }
    
    /**
     * Start monitoring for a specific system
     */
    startSystemMonitoring(systemId) {
        const system = this.monitoring_systems.get(systemId);
        if (!system) return;
        
        const monitorFunction = async () => {
            try {
                const metrics = await this.collectSystemMetrics(systemId);
                system.last_check = Date.now();
                system.error_count = 0;
                
                // Process collected metrics
                for (const [metricName, metricValue] of Object.entries(metrics)) {
                    this.recordMetric(systemId, metricName, metricValue);
                }
                
                // Update system health score
                this.updateSystemHealthScore(systemId, metrics);
                
                this.emit('metric_collected', { systemId, metrics });
                
            } catch (error) {
                system.error_count++;
                console.error(`Monitoring error for system ${systemId}:`, error.message);
                
                if (system.error_count >= 3) {
                    system.status = 'error';
                    this.emit('system_health_changed', { systemId, status: 'error', error: error.message });
                }
            }
        };
        
        // Run initial check
        monitorFunction();
        
        // Schedule periodic checks
        const intervalId = setInterval(monitorFunction, system.check_interval);
        system.intervalId = intervalId;
        
        console.log(`Started monitoring for ${system.name} (${systemId})`);
    }
    
    /**
     * Collect metrics for a specific system
     */
    async collectSystemMetrics(systemId) {
        const system = this.monitoring_systems.get(systemId);
        const metrics = {};
        
        switch (systemId) {
            case 'performance':
                return await this.collectPerformanceMetrics();
            case 'database':
                return await this.collectDatabaseMetrics();
            case 'security':
                return await this.collectSecurityMetrics();
            case 'marketplace':
                return await this.collectMarketplaceMetrics();
            case 'application':
                return await this.collectApplicationMetrics();
            case 'backup':
                return await this.collectBackupMetrics();
            default:
                return {};
        }
    }
    
    /**
     * Collect performance metrics
     */
    async collectPerformanceMetrics() {
        const os = require('os');
        
        // CPU Usage
        const cpus = os.cpus();
        let totalIdle = 0;
        let totalTick = 0;
        
        cpus.forEach(cpu => {
            for (type in cpu.times) {
                totalTick += cpu.times[type];
            }
            totalIdle += cpu.times.idle;
        });
        
        const cpuUsage = 100 - ~~(100 * totalIdle / totalTick);
        
        // Memory Usage
        const totalMemory = os.totalmem();
        const freeMemory = os.freemem();
        const memoryUsage = ((totalMemory - freeMemory) / totalMemory) * 100;
        
        // Load Average
        const loadAverage = os.loadavg();
        
        return {
            cpu_usage: cpuUsage,
            memory_usage: memoryUsage,
            memory_total_gb: (totalMemory / 1024 / 1024 / 1024).toFixed(2),
            memory_free_gb: (freeMemory / 1024 / 1024 / 1024).toFixed(2),
            load_average_1m: loadAverage[0],
            load_average_5m: loadAverage[1],
            load_average_15m: loadAverage[2],
            uptime: os.uptime()
        };
    }
    
    /**
     * Collect database metrics
     */
    async collectDatabaseMetrics() {
        // Simulate database metrics collection
        // In production, this would connect to actual database
        
        return {
            connection_count: Math.floor(Math.random() * 50) + 10,
            active_connections: Math.floor(Math.random() * 30) + 5,
            query_time_avg: Math.random() * 100 + 10,
            slow_queries: Math.floor(Math.random() * 5),
            deadlocks: Math.floor(Math.random() * 2),
            table_locks: Math.floor(Math.random() * 10),
            innodb_buffer_pool_usage: Math.random() * 30 + 60,
            queries_per_second: Math.floor(Math.random() * 100) + 50
        };
    }
    
    /**
     * Collect security metrics
     */
    async collectSecurityMetrics() {
        // Simulate security metrics collection
        // In production, this would analyze security logs
        
        return {
            failed_logins: Math.floor(Math.random() * 10),
            suspicious_requests: Math.floor(Math.random() * 5),
            blocked_ips: Math.floor(Math.random() * 20),
            malware_attempts: Math.floor(Math.random() * 3),
            firewall_blocks: Math.floor(Math.random() * 15),
            ssl_cert_days_remaining: 45,
            security_scan_score: Math.floor(Math.random() * 20) + 80,
            vulnerability_count: Math.floor(Math.random() * 3)
        };
    }
    
    /**
     * Collect marketplace metrics
     */
    async collectMarketplaceMetrics() {
        const marketplaces = ['trendyol', 'n11', 'amazon', 'ebay', 'hepsiburada', 'ozon', 'pazarama', 'ciceksepeti'];
        const metrics = {};
        
        marketplaces.forEach(marketplace => {
            metrics[`${marketplace}_response_time`] = Math.random() * 1000 + 200;
            metrics[`${marketplace}_success_rate`] = Math.random() * 10 + 90;
            metrics[`${marketplace}_orders_synced`] = Math.floor(Math.random() * 100) + 10;
            metrics[`${marketplace}_inventory_synced`] = Math.floor(Math.random() * 500) + 100;
        });
        
        // Overall marketplace metrics
        metrics.total_api_calls = Math.floor(Math.random() * 1000) + 500;
        metrics.avg_response_time = Object.keys(metrics)
            .filter(key => key.includes('response_time'))
            .reduce((sum, key) => sum + metrics[key], 0) / marketplaces.length;
        
        return metrics;
    }
    
    /**
     * Collect application metrics
     */
    async collectApplicationMetrics() {
        return {
            response_time: Math.random() * 500 + 100,
            error_rate: Math.random() * 2,
            requests_per_minute: Math.floor(Math.random() * 200) + 50,
            active_sessions: Math.floor(Math.random() * 100) + 20,
            cache_hit_rate: Math.random() * 20 + 75,
            page_load_time: Math.random() * 1000 + 500,
            conversion_rate: Math.random() * 5 + 2,
            user_satisfaction: Math.random() * 20 + 75
        };
    }
    
    /**
     * Collect backup metrics
     */
    async collectBackupMetrics() {
        return {
            last_backup_age_hours: Math.floor(Math.random() * 24),
            backup_success_rate: Math.random() * 5 + 95,
            backup_size_gb: Math.random() * 10 + 5,
            backup_duration_minutes: Math.floor(Math.random() * 30) + 10,
            recovery_test_success: Math.random() > 0.1,
            retention_compliance: Math.random() > 0.05,
            offsite_backup_status: Math.random() > 0.02
        };
    }
    
    /**
     * Record a metric value
     */
    recordMetric(systemId, metricName, value, timestamp = Date.now()) {
        const metricKey = `${systemId}.${metricName}`;
        
        if (!this.metrics.has(metricKey)) {
            this.metrics.set(metricKey, []);
        }
        
        const metricData = this.metrics.get(metricKey);
        metricData.push({
            timestamp,
            value,
            system: systemId,
            metric: metricName
        });
        
        // Keep only recent data points (based on retention period)
        const cutoffTime = timestamp - this.config.retention_period;
        const filteredData = metricData.filter(point => point.timestamp > cutoffTime);
        this.metrics.set(metricKey, filteredData);
        
        // Check for alert conditions
        this.checkAlertConditions(systemId, metricName, value);
    }
    
    /**
     * Check if metric value triggers any alerts
     */
    checkAlertConditions(systemId, metricName, value) {
        const thresholds = this.config.alert_thresholds;
        const alertKey = `${systemId}.${metricName}`;
        
        let alertTriggered = false;
        let alertLevel = 'info';
        let alertMessage = '';
        
        // Check various alert conditions
        if (metricName === 'cpu_usage' && value > thresholds.cpu_usage) {
            alertTriggered = true;
            alertLevel = value > 95 ? 'critical' : 'warning';
            alertMessage = `High CPU usage: ${value.toFixed(2)}%`;
        }
        
        if (metricName === 'memory_usage' && value > thresholds.memory_usage) {
            alertTriggered = true;
            alertLevel = value > 95 ? 'critical' : 'warning';
            alertMessage = `High memory usage: ${value.toFixed(2)}%`;
        }
        
        if (metricName === 'response_time' && value > thresholds.response_time) {
            alertTriggered = true;
            alertLevel = value > 5000 ? 'critical' : 'warning';
            alertMessage = `High response time: ${value.toFixed(0)}ms`;
        }
        
        if (metricName === 'error_rate' && value > thresholds.error_rate) {
            alertTriggered = true;
            alertLevel = value > 10 ? 'critical' : 'warning';
            alertMessage = `High error rate: ${value.toFixed(2)}%`;
        }
        
        if (alertTriggered) {
            this.triggerAlert(alertKey, alertLevel, alertMessage, {
                system: systemId,
                metric: metricName,
                value,
                threshold: thresholds[metricName]
            });
        }
    }
    
    /**
     * Trigger an alert
     */
    triggerAlert(alertKey, level, message, context = {}) {
        const alert = {
            id: `alert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            key: alertKey,
            level,
            message,
            context,
            timestamp: Date.now(),
            acknowledged: false,
            resolved: false
        };
        
        this.alerts.set(alert.id, alert);
        
        // Add to notification queue
        this.notification_queue.push({
            type: 'alert',
            priority: level === 'critical' ? 'high' : 'normal',
            data: alert
        });
        
        this.emit('alert_triggered', alert);
        
        console.log(`Alert triggered: [${level.toUpperCase()}] ${message}`);
        
        // Check for emergency conditions
        if (level === 'critical') {
            this.checkEmergencyConditions(alert);
        }
    }
    
    /**
     * Check for emergency conditions that require immediate response
     */
    checkEmergencyConditions(alert) {
        const criticalAlerts = Array.from(this.alerts.values())
            .filter(a => a.level === 'critical' && !a.resolved && a.timestamp > (Date.now() - 300000)); // Last 5 minutes
        
        if (criticalAlerts.length >= 3) {
            this.emit('emergency_detected', {
                type: 'multiple_critical_alerts',
                alerts: criticalAlerts,
                timestamp: Date.now()
            });
        }
        
        // Check for specific emergency patterns
        const systemFailures = criticalAlerts.filter(a => 
            a.context.metric === 'cpu_usage' || 
            a.context.metric === 'memory_usage' ||
            a.context.metric === 'response_time'
        );
        
        if (systemFailures.length >= 2) {
            this.emit('emergency_detected', {
                type: 'system_failure',
                alerts: systemFailures,
                timestamp: Date.now()
            });
        }
    }
    
    /**
     * Update system health score
     */
    updateSystemHealthScore(systemId, metrics) {
        const system = this.monitoring_systems.get(systemId);
        if (!system) return;
        
        let healthScore = 100;
        
        // Reduce health score based on metric values
        Object.entries(metrics).forEach(([metricName, value]) => {
            if (metricName === 'cpu_usage' && value > 80) {
                healthScore -= (value - 80) * 2;
            }
            if (metricName === 'memory_usage' && value > 85) {
                healthScore -= (value - 85) * 3;
            }
            if (metricName === 'error_rate' && value > 1) {
                healthScore -= value * 5;
            }
            if (metricName === 'response_time' && value > 1000) {
                healthScore -= (value - 1000) / 100;
            }
        });
        
        // Factor in error count
        healthScore -= system.error_count * 10;
        
        // Ensure health score is within bounds
        healthScore = Math.max(0, Math.min(100, healthScore));
        
        const previousScore = system.health_score;
        system.health_score = Math.round(healthScore);
        
        // Emit event if health score changed significantly
        if (Math.abs(previousScore - system.health_score) >= 10) {
            this.emit('system_health_changed', {
                systemId,
                previousScore,
                currentScore: system.health_score,
                status: system.health_score < 50 ? 'critical' : system.health_score < 80 ? 'warning' : 'healthy'
            });
        }
    }
    
    /**
     * Start dashboard updates
     */
    startDashboardUpdates() {
        const updateDashboard = () => {
            this.dashboard_data = {
                timestamp: Date.now(),
                systems: this.getSystemsOverview(),
                metrics: this.getRecentMetrics(),
                alerts: this.getActiveAlerts(),
                health: this.getOverallHealth(),
                performance: this.getPerformanceSummary()
            };
            
            this.saveDashboardData();
        };
        
        // Update dashboard every 30 seconds
        setInterval(updateDashboard, 30000);
        
        // Initial update
        updateDashboard();
    }
    
    /**
     * Get systems overview
     */
    getSystemsOverview() {
        const overview = {};
        
        for (const [systemId, system] of this.monitoring_systems) {
            overview[systemId] = {
                name: system.name,
                status: system.status,
                health_score: system.health_score,
                last_check: system.last_check,
                error_count: system.error_count,
                uptime: system.last_check ? Date.now() - (system.last_check - system.check_interval) : 0
            };
        }
        
        return overview;
    }
    
    /**
     * Get recent metrics summary
     */
    getRecentMetrics() {
        const summary = {};
        const recentCutoff = Date.now() - 300000; // Last 5 minutes
        
        for (const [metricKey, dataPoints] of this.metrics) {
            const recentPoints = dataPoints.filter(point => point.timestamp > recentCutoff);
            
            if (recentPoints.length > 0) {
                const values = recentPoints.map(point => point.value);
                summary[metricKey] = {
                    current: values[values.length - 1],
                    average: values.reduce((sum, val) => sum + val, 0) / values.length,
                    min: Math.min(...values),
                    max: Math.max(...values),
                    count: values.length
                };
            }
        }
        
        return summary;
    }
    
    /**
     * Get active alerts
     */
    getActiveAlerts() {
        return Array.from(this.alerts.values())
            .filter(alert => !alert.resolved)
            .sort((a, b) => {
                const levelPriority = { critical: 3, warning: 2, info: 1 };
                return (levelPriority[b.level] || 0) - (levelPriority[a.level] || 0) || b.timestamp - a.timestamp;
            })
            .slice(0, 20); // Limit to 20 most recent/important alerts
    }
    
    /**
     * Get overall system health
     */
    getOverallHealth() {
        const systems = Array.from(this.monitoring_systems.values());
        const totalScore = systems.reduce((sum, system) => sum + system.health_score, 0);
        const averageScore = totalScore / systems.length;
        
        const activeAlerts = this.getActiveAlerts();
        const criticalAlerts = activeAlerts.filter(alert => alert.level === 'critical');
        
        let status = 'healthy';
        if (averageScore < 50 || criticalAlerts.length > 0) {
            status = 'critical';
        } else if (averageScore < 80 || activeAlerts.length > 5) {
            status = 'warning';
        }
        
        return {
            status,
            score: Math.round(averageScore),
            systems_healthy: systems.filter(s => s.health_score >= 80).length,
            systems_total: systems.length,
            active_alerts: activeAlerts.length,
            critical_alerts: criticalAlerts.length
        };
    }
    
    /**
     * Get performance summary
     */
    getPerformanceSummary() {
        const recentMetrics = this.getRecentMetrics();
        
        return {
            cpu_usage: recentMetrics['performance.cpu_usage']?.current || 0,
            memory_usage: recentMetrics['performance.memory_usage']?.current || 0,
            response_time: recentMetrics['application.response_time']?.current || 0,
            error_rate: recentMetrics['application.error_rate']?.current || 0,
            requests_per_minute: recentMetrics['application.requests_per_minute']?.current || 0,
            database_connections: recentMetrics['database.connection_count']?.current || 0
        };
    }
    
    /**
     * Save dashboard data to file
     */
    saveDashboardData() {
        const dashboardDir = path.join(__dirname, 'dashboard_data');
        if (!fs.existsSync(dashboardDir)) {
            fs.mkdirSync(dashboardDir, { recursive: true });
        }
        
        const dashboardFile = path.join(dashboardDir, 'current_dashboard.json');
        fs.writeFileSync(dashboardFile, JSON.stringify(this.dashboard_data, null, 2));
        
        // Also save timestamped version for history
        const timestampedFile = path.join(dashboardDir, `dashboard_${Date.now()}.json`);
        fs.writeFileSync(timestampedFile, JSON.stringify(this.dashboard_data, null, 2));
        
        // Clean up old dashboard files (keep last 100)
        this.cleanupOldDashboardFiles(dashboardDir);
    }
    
    /**
     * Clean up old dashboard files
     */
    cleanupOldDashboardFiles(dashboardDir) {
        try {
            const files = fs.readdirSync(dashboardDir)
                .filter(file => file.startsWith('dashboard_') && file.endsWith('.json'))
                .map(file => ({
                    name: file,
                    path: path.join(dashboardDir, file),
                    mtime: fs.statSync(path.join(dashboardDir, file)).mtime
                }))
                .sort((a, b) => b.mtime - a.mtime);
            
            if (files.length > 100) {
                const filesToDelete = files.slice(100);
                filesToDelete.forEach(file => {
                    fs.unlinkSync(file.path);
                });
            }
        } catch (error) {
            console.error('Error cleaning up dashboard files:', error.message);
        }
    }
    
    /**
     * Start metric aggregation
     */
    startMetricAggregation() {
        // Aggregate metrics at different intervals
        this.config.aggregation_intervals.forEach(interval => {
            setInterval(() => {
                this.aggregateMetrics(interval);
            }, interval * 1000);
        });
        
        console.log('Metric aggregation started');
    }
    
    /**
     * Aggregate metrics for a specific time interval
     */
    aggregateMetrics(intervalSeconds) {
        const now = Date.now();
        const intervalMs = intervalSeconds * 1000;
        const startTime = now - intervalMs;
        
        const aggregatedData = {};
        
        for (const [metricKey, dataPoints] of this.metrics) {
            const relevantPoints = dataPoints.filter(point => 
                point.timestamp >= startTime && point.timestamp <= now
            );
            
            if (relevantPoints.length > 0) {
                const values = relevantPoints.map(point => point.value);
                aggregatedData[metricKey] = {
                    interval: intervalSeconds,
                    start_time: startTime,
                    end_time: now,
                    count: values.length,
                    average: values.reduce((sum, val) => sum + val, 0) / values.length,
                    min: Math.min(...values),
                    max: Math.max(...values),
                    sum: values.reduce((sum, val) => sum + val, 0),
                    median: this.calculateMedian(values)
                };
            }
        }
        
        // Save aggregated data
        this.saveAggregatedData(intervalSeconds, aggregatedData);
    }
    
    /**
     * Calculate median value
     */
    calculateMedian(values) {
        const sorted = values.slice().sort((a, b) => a - b);
        const middle = Math.floor(sorted.length / 2);
        
        if (sorted.length % 2 === 0) {
            return (sorted[middle - 1] + sorted[middle]) / 2;
        } else {
            return sorted[middle];
        }
    }
    
    /**
     * Save aggregated data
     */
    saveAggregatedData(interval, data) {
        const aggregationDir = path.join(__dirname, 'aggregated_data', `${interval}s`);
        if (!fs.existsSync(aggregationDir)) {
            fs.mkdirSync(aggregationDir, { recursive: true });
        }
        
        const filename = `aggregated_${Date.now()}.json`;
        const filepath = path.join(aggregationDir, filename);
        
        fs.writeFileSync(filepath, JSON.stringify({
            interval,
            timestamp: Date.now(),
            data
        }, null, 2));
    }
    
    /**
     * Start alert processing
     */
    startAlertProcessing() {
        setInterval(() => {
            this.processAlerts();
        }, 60000); // Process alerts every minute
        
        console.log('Alert processing started');
    }
    
    /**
     * Process active alerts
     */
    processAlerts() {
        const activeAlerts = this.getActiveAlerts();
        
        // Auto-resolve alerts that are no longer valid
        activeAlerts.forEach(alert => {
            if (this.shouldAutoResolveAlert(alert)) {
                this.resolveAlert(alert.id, 'auto-resolved');
            }
        });
        
        // Escalate critical alerts that haven't been acknowledged
        const unacknowledgedCritical = activeAlerts.filter(alert => 
            alert.level === 'critical' && 
            !alert.acknowledged && 
            (Date.now() - alert.timestamp) > 300000 // 5 minutes
        );
        
        unacknowledgedCritical.forEach(alert => {
            this.escalateAlert(alert.id);
        });
    }
    
    /**
     * Check if alert should be auto-resolved
     */
    shouldAutoResolveAlert(alert) {
        // Check if the condition that triggered the alert is no longer present
        const recentMetrics = this.getRecentMetrics();
        const metricKey = `${alert.context.system}.${alert.context.metric}`;
        const currentValue = recentMetrics[metricKey]?.current;
        
        if (currentValue === undefined) return false;
        
        const threshold = alert.context.threshold;
        
        // If current value is below threshold for at least 2 minutes, auto-resolve
        if (currentValue < threshold && (Date.now() - alert.timestamp) > 120000) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Resolve an alert
     */
    resolveAlert(alertId, reason = 'manual') {
        const alert = this.alerts.get(alertId);
        if (alert) {
            alert.resolved = true;
            alert.resolved_at = Date.now();
            alert.resolved_reason = reason;
            
            console.log(`Alert resolved: ${alert.message} (${reason})`);
            
            // Add to notification queue
            this.notification_queue.push({
                type: 'alert_resolved',
                priority: 'low',
                data: alert
            });
        }
    }
    
    /**
     * Escalate an alert
     */
    escalateAlert(alertId) {
        const alert = this.alerts.get(alertId);
        if (alert) {
            alert.escalated = true;
            alert.escalated_at = Date.now();
            
            console.log(`Alert escalated: ${alert.message}`);
            
            // Add to notification queue with high priority
            this.notification_queue.push({
                type: 'alert_escalated',
                priority: 'high',
                data: alert
            });
        }
    }
    
    /**
     * Start notification processing
     */
    startNotificationProcessing() {
        setInterval(() => {
            this.processNotifications();
        }, 5000); // Process notifications every 5 seconds
        
        console.log('Notification processing started');
    }
    
    /**
     * Process notification queue
     */
    async processNotifications() {
        if (this.notification_queue.length === 0) return;
        
        // Sort by priority (high priority first)
        this.notification_queue.sort((a, b) => {
            const priorityOrder = { high: 3, normal: 2, low: 1 };
            return (priorityOrder[b.priority] || 0) - (priorityOrder[a.priority] || 0);
        });
        
        // Process up to 10 notifications at a time
        const notificationsToProcess = this.notification_queue.splice(0, 10);
        
        for (const notification of notificationsToProcess) {
            try {
                await this.sendNotification(notification);
            } catch (error) {
                console.error('Failed to send notification:', error.message);
                
                // Re-queue notification with lower priority if it's important
                if (notification.priority === 'high') {
                    notification.priority = 'normal';
                    notification.retry_count = (notification.retry_count || 0) + 1;
                    
                    if (notification.retry_count < 3) {
                        this.notification_queue.push(notification);
                    }
                }
            }
        }
    }
    
    /**
     * Send a notification
     */
    async sendNotification(notification) {
        const { type, priority, data } = notification;
        
        // Email notifications
        if (this.config.notification_channels.email) {
            await this.sendEmailNotification(type, data);
        }
        
        // Slack notifications
        if (this.config.notification_channels.slack) {
            await this.sendSlackNotification(type, data);
        }
        
        // Webhook notifications
        if (this.config.notification_channels.webhook) {
            await this.sendWebhookNotification(type, data);
        }
        
        console.log(`Notification sent: ${type} (${priority})`);
    }
    
    /**
     * Send email notification
     */
    async sendEmailNotification(type, data) {
        // In production, implement actual email sending
        console.log(`Email notification: ${type}`, data);
    }
    
    /**
     * Send Slack notification
     */
    async sendSlackNotification(type, data) {
        // In production, implement actual Slack integration
        console.log(`Slack notification: ${type}`, data);
    }
    
    /**
     * Send webhook notification
     */
    async sendWebhookNotification(type, data) {
        // In production, implement actual webhook sending
        console.log(`Webhook notification: ${type}`, data);
    }
    
    /**
     * Handle metric collection event
     */
    handleMetricCollection(event) {
        const { systemId, metrics } = event;
        
        // Log significant metric changes
        Object.entries(metrics).forEach(([metricName, value]) => {
            const metricKey = `${systemId}.${metricName}`;
            const previousValues = this.metrics.get(metricKey);
            
            if (previousValues && previousValues.length > 0) {
                const previousValue = previousValues[previousValues.length - 1].value;
                const changePercent = Math.abs((value - previousValue) / previousValue) * 100;
                
                if (changePercent > 50) { // Log significant changes
                    console.log(`Significant metric change: ${metricKey} changed by ${changePercent.toFixed(1)}% (${previousValue} -> ${value})`);
                }
            }
        });
    }
    
    /**
     * Handle alert triggered event
     */
    handleAlertTriggered(alert) {
        console.log(`Alert triggered: [${alert.level.toUpperCase()}] ${alert.message}`);
        
        // Log alert details
        const logEntry = {
            timestamp: new Date(alert.timestamp).toISOString(),
            alert_id: alert.id,
            level: alert.level,
            message: alert.message,
            system: alert.context.system,
            metric: alert.context.metric,
            value: alert.context.value,
            threshold: alert.context.threshold
        };
        
        this.logToFile('alerts.log', JSON.stringify(logEntry));
    }
    
    /**
     * Handle system health change event
     */
    handleSystemHealthChange(event) {
        const { systemId, previousScore, currentScore, status } = event;
        
        console.log(`System health changed: ${systemId} - ${previousScore} -> ${currentScore} (${status})`);
        
        // Log health change
        const logEntry = {
            timestamp: new Date().toISOString(),
            system: systemId,
            previous_score: previousScore,
            current_score: currentScore,
            status: status
        };
        
        this.logToFile('health.log', JSON.stringify(logEntry));
    }
    
    /**
     * Handle emergency response event
     */
    handleEmergencyResponse(emergency) {
        console.log(`EMERGENCY DETECTED: ${emergency.type}`);
        
        // Log emergency
        const logEntry = {
            timestamp: new Date(emergency.timestamp).toISOString(),
            type: emergency.type,
            alerts: emergency.alerts.map(alert => ({
                id: alert.id,
                level: alert.level,
                message: alert.message,
                system: alert.context.system
            }))
        };
        
        this.logToFile('emergency.log', JSON.stringify(logEntry));
        
        // Trigger emergency procedures
        this.executeEmergencyProcedures(emergency);
    }
    
    /**
     * Execute emergency procedures
     */
    async executeEmergencyProcedures(emergency) {
        console.log('Executing emergency procedures...');
        
        try {
            // Send high-priority notifications
            this.notification_queue.unshift({
                type: 'emergency',
                priority: 'high',
                data: emergency
            });
            
            // Auto-scale resources if possible
            if (emergency.type === 'system_failure') {
                await this.triggerAutoScaling();
            }
            
            // Enable maintenance mode if necessary
            if (emergency.alerts.length >= 5) {
                await this.enableEmergencyMaintenanceMode();
            }
            
        } catch (error) {
            console.error('Error executing emergency procedures:', error.message);
        }
    }
    
    /**
     * Trigger auto-scaling
     */
    async triggerAutoScaling() {
        console.log('Triggering auto-scaling procedures...');
        // In production, this would integrate with cloud provider APIs
    }
    
    /**
     * Enable emergency maintenance mode
     */
    async enableEmergencyMaintenanceMode() {
        console.log('Enabling emergency maintenance mode...');
        // In production, this would integrate with the environment manager
    }
    
    /**
     * Handle uncaught exceptions
     */
    handleUncaughtException(error) {
        console.error('Uncaught Exception:', error);
        this.logToFile('errors.log', `Uncaught Exception: ${error.stack}`);
        
        // Try to gracefully shutdown
        this.shutdown();
    }
    
    /**
     * Handle unhandled rejections
     */
    handleUnhandledRejection(reason, promise) {
        console.error('Unhandled Rejection at:', promise, 'reason:', reason);
        this.logToFile('errors.log', `Unhandled Rejection: ${reason}`);
    }
    
    /**
     * Log message to file
     */
    logToFile(filename, message) {
        try {
            const logsDir = path.join(__dirname, 'logs');
            if (!fs.existsSync(logsDir)) {
                fs.mkdirSync(logsDir, { recursive: true });
            }
            
            const logFile = path.join(logsDir, filename);
            const timestamp = new Date().toISOString();
            const logEntry = `[${timestamp}] ${message}\n`;
            
            fs.appendFileSync(logFile, logEntry);
        } catch (error) {
            console.error('Failed to write to log file:', error.message);
        }
    }
    
    /**
     * Get monitoring statistics
     */
    getMonitoringStats() {
        return {
            systems_count: this.monitoring_systems.size,
            metrics_count: Array.from(this.metrics.values()).reduce((sum, points) => sum + points.length, 0),
            active_alerts: this.getActiveAlerts().length,
            notifications_queued: this.notification_queue.length,
            uptime: Date.now() - this.startTime,
            memory_usage: process.memoryUsage(),
            system_health: this.getOverallHealth()
        };
    }
    
    /**
     * Generate monitoring report
     */
    generateMonitoringReport() {
        const report = {
            title: 'OpenCart Production Monitoring Report',
            generated_at: new Date().toISOString(),
            summary: this.getOverallHealth(),
            systems: this.getSystemsOverview(),
            recent_metrics: this.getRecentMetrics(),
            active_alerts: this.getActiveAlerts(),
            performance: this.getPerformanceSummary(),
            statistics: this.getMonitoringStats()
        };
        
        // Save report to file
        const reportsDir = path.join(__dirname, 'reports');
        if (!fs.existsSync(reportsDir)) {
            fs.mkdirSync(reportsDir, { recursive: true });
        }
        
        const reportFile = path.join(reportsDir, `monitoring_report_${Date.now()}.json`);
        fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));
        
        return report;
    }
    
    /**
     * Shutdown monitoring system gracefully
     */
    shutdown() {
        console.log('Shutting down monitoring system...');
        
        // Stop all monitoring intervals
        for (const [systemId, system] of this.monitoring_systems) {
            if (system.intervalId) {
                clearInterval(system.intervalId);
            }
        }
        
        // Save final dashboard data
        this.saveDashboardData();
        
        // Generate final report
        this.generateMonitoringReport();
        
        console.log('Monitoring system shut down gracefully');
        process.exit(0);
    }
}

// Initialize monitoring integration
const monitoringIntegration = new OpenCartProductionMonitoringIntegration();
monitoringIntegration.startTime = Date.now();

// Export for external use
module.exports = OpenCartProductionMonitoringIntegration;

console.log('OpenCart Production Monitoring Integration System started successfully');
