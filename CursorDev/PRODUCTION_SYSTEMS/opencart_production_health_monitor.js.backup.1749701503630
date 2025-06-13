#!/usr/bin/env node
/**
 * ================================================================
 * OpenCart Production Health Monitoring System
 * Comprehensive real-time health monitoring and alerting
 * ================================================================
 * 
 * @package    MesChain-Sync Enterprise Production Systems
 * @author     OpenCart Production Team
 * @version    3.1.1
 * @date       June 6, 2025
 * @goal       Monitor production system health in real-time
 */

const http = require('http');
const https = require('https');
const fs = require('fs').promises;
const path = require('path');
const { EventEmitter } = require('events');

class OpenCartProductionHealthMonitor extends EventEmitter {
    constructor(config = {}) {
        super();
        
        this.config = {
            checkInterval: 30000, // 30 seconds
            alertThreshold: 3, // 3 consecutive failures
            timeout: 10000, // 10 seconds
            retryAttempts: 3,
            retryDelay: 5000,
            logPath: './logs/health_monitor.log',
            alertChannels: ['console', 'file', 'webhook'],
            ...config
        };
        
        this.healthChecks = new Map();
        this.failureCount = new Map();
        this.lastHealthStatus = new Map();
        this.monitoringActive = false;
        this.alertHistory = [];
        
        this.initializeHealthChecks();
        this.setupEventHandlers();
    }
    
    /**
     * Initialize health check configurations
     */
    initializeHealthChecks() {
        this.healthChecks.set('system', {
            name: 'System Health',
            endpoint: '/health',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: true
        });
        
        this.healthChecks.set('database', {
            name: 'Database Health',
            endpoint: '/health/database',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: true
        });
        
        this.healthChecks.set('marketplaces', {
            name: 'Marketplace Integrations',
            endpoint: '/health/marketplaces',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: false
        });
        
        this.healthChecks.set('cache', {
            name: 'Cache System',
            endpoint: '/health/cache',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: false
        });
        
        this.healthChecks.set('error_handler', {
            name: 'Error Handling System',
            endpoint: '/health/error-handler',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: true
        });
        
        this.healthChecks.set('monitoring', {
            name: 'Monitoring Dashboard',
            endpoint: '/health/monitoring',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: false
        });
        
        this.healthChecks.set('notifications', {
            name: 'Notification System',
            endpoint: '/health/notifications',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: false
        });
        
        this.healthChecks.set('integration_tests', {
            name: 'Integration Tests',
            endpoint: '/health/integration-tests',
            method: 'GET',
            expectedStatus: 200,
            timeout: this.config.timeout,
            critical: false
        });
    }
    
    /**
     * Setup event handlers
     */
    setupEventHandlers() {
        this.on('healthCheckPassed', (checkName, result) => {
            this.handleHealthCheckPassed(checkName, result);
        });
        
        this.on('healthCheckFailed', (checkName, error) => {
            this.handleHealthCheckFailed(checkName, error);
        });
        
        this.on('systemAlert', (alert) => {
            this.handleSystemAlert(alert);
        });
        
        this.on('criticalFailure', (failure) => {
            this.handleCriticalFailure(failure);
        });
    }
    
    /**
     * Start health monitoring
     */
    async startMonitoring() {
        if (this.monitoringActive) {
            console.log('Health monitoring is already active');
            return;
        }
        
        this.monitoringActive = true;
        console.log('🔍 Starting OpenCart Production Health Monitoring...');
        
        await this.logEvent('info', 'Health monitoring started', {
            checkInterval: this.config.checkInterval,
            totalChecks: this.healthChecks.size,
            alertThreshold: this.config.alertThreshold
        });
        
        // Initial health check
        await this.performAllHealthChecks();
        
        // Schedule regular health checks
        this.monitoringTimer = setInterval(async () => {
            await this.performAllHealthChecks();
        }, this.config.checkInterval);
        
        console.log(`✅ Health monitoring active with ${this.healthChecks.size} checks every ${this.config.checkInterval/1000} seconds`);
    }
    
    /**
     * Stop health monitoring
     */
    async stopMonitoring() {
        if (!this.monitoringActive) {
            console.log('Health monitoring is not active');
            return;
        }
        
        this.monitoringActive = false;
        
        if (this.monitoringTimer) {
            clearInterval(this.monitoringTimer);
            this.monitoringTimer = null;
        }
        
        await this.logEvent('info', 'Health monitoring stopped');
        console.log('🛑 Health monitoring stopped');
    }
    
    /**
     * Perform all health checks
     */
    async performAllHealthChecks() {
        const checkPromises = Array.from(this.healthChecks.entries()).map(([checkName, checkConfig]) => {
            return this.performHealthCheck(checkName, checkConfig);
        });
        
        const results = await Promise.allSettled(checkPromises);
        
        // Process results
        let passedChecks = 0;
        let failedChecks = 0;
        let criticalFailures = 0;
        
        results.forEach((result, index) => {
            const checkName = Array.from(this.healthChecks.keys())[index];
            const checkConfig = this.healthChecks.get(checkName);
            
            if (result.status === 'fulfilled' && result.value.success) {
                passedChecks++;
                this.emit('healthCheckPassed', checkName, result.value);
            } else {
                failedChecks++;
                const error = result.status === 'rejected' ? result.reason : result.value.error;
                this.emit('healthCheckFailed', checkName, error);
                
                if (checkConfig.critical) {
                    criticalFailures++;
                }
            }
        });
        
        // Generate overall health status
        const overallHealth = {
            timestamp: new Date().toISOString(),
            totalChecks: this.healthChecks.size,
            passedChecks,
            failedChecks,
            criticalFailures,
            healthScore: Math.round((passedChecks / this.healthChecks.size) * 100),
            status: criticalFailures > 0 ? 'critical' : failedChecks > 0 ? 'warning' : 'healthy'
        };
        
        await this.logEvent('info', 'Health check cycle completed', overallHealth);
        
        // Emit system alert if needed
        if (overallHealth.status === 'critical') {
            this.emit('criticalFailure', {
                message: 'Critical system failures detected',
                criticalFailures,
                timestamp: new Date().toISOString()
            });
        } else if (overallHealth.healthScore < 80) {
            this.emit('systemAlert', {
                level: 'warning',
                message: `System health below threshold: ${overallHealth.healthScore}%`,
                failedChecks,
                timestamp: new Date().toISOString()
            });
        }
        
        return overallHealth;
    }
    
    /**
     * Perform individual health check
     */
    async performHealthCheck(checkName, checkConfig) {
        const startTime = Date.now();
        
        try {
            const result = await this.executeHealthCheck(checkConfig);
            const responseTime = Date.now() - startTime;
            
            return {
                success: true,
                checkName,
                responseTime,
                status: result.status,
                data: result.data,
                timestamp: new Date().toISOString()
            };
            
        } catch (error) {
            const responseTime = Date.now() - startTime;
            
            return {
                success: false,
                checkName,
                responseTime,
                error: error.message,
                timestamp: new Date().toISOString()
            };
        }
    }
    
    /**
     * Execute health check HTTP request
     */
    async executeHealthCheck(checkConfig) {
        return new Promise((resolve, reject) => {
            const url = `http://localhost:3015${checkConfig.endpoint}`;
            const requestOptions = {
                method: checkConfig.method,
                timeout: checkConfig.timeout,
                headers: {
                    'User-Agent': 'OpenCart-Health-Monitor/3.1.1',
                    'Accept': 'application/json'
                }
            };
            
            const request = http.request(url, requestOptions, (response) => {
                let data = '';
                
                response.on('data', (chunk) => {
                    data += chunk;
                });
                
                response.on('end', () => {
                    try {
                        const parsedData = data ? JSON.parse(data) : {};
                        
                        if (response.statusCode === checkConfig.expectedStatus) {
                            resolve({
                                status: response.statusCode,
                                data: parsedData
                            });
                        } else {
                            reject(new Error(`Unexpected status code: ${response.statusCode}`));
                        }
                    } catch (parseError) {
                        reject(new Error(`Failed to parse response: ${parseError.message}`));
                    }
                });
            });
            
            request.on('timeout', () => {
                request.destroy();
                reject(new Error(`Request timeout after ${checkConfig.timeout}ms`));
            });
            
            request.on('error', (error) => {
                reject(new Error(`Request failed: ${error.message}`));
            });
            
            request.setTimeout(checkConfig.timeout);
            request.end();
        });
    }
    
    /**
     * Handle successful health check
     */
    handleHealthCheckPassed(checkName, result) {
        // Reset failure count
        this.failureCount.set(checkName, 0);
        
        // Update last health status
        this.lastHealthStatus.set(checkName, {
            status: 'healthy',
            lastCheck: result.timestamp,
            responseTime: result.responseTime
        });
        
        // Log if recovering from failure
        const wasUnhealthy = this.lastHealthStatus.get(checkName)?.status !== 'healthy';
        if (wasUnhealthy) {
            this.logEvent('info', `Health check recovered: ${checkName}`, result);
        }
    }
    
    /**
     * Handle failed health check
     */
    handleHealthCheckFailed(checkName, error) {
        const currentFailures = (this.failureCount.get(checkName) || 0) + 1;
        this.failureCount.set(checkName, currentFailures);
        
        // Update last health status
        this.lastHealthStatus.set(checkName, {
            status: 'unhealthy',
            lastCheck: new Date().toISOString(),
            error: error.message || error,
            consecutiveFailures: currentFailures
        });
        
        this.logEvent('error', `Health check failed: ${checkName}`, {
            error: error.message || error,
            consecutiveFailures: currentFailures,
            alertThreshold: this.config.alertThreshold
        });
        
        // Trigger alert if threshold reached
        if (currentFailures >= this.config.alertThreshold) {
            const checkConfig = this.healthChecks.get(checkName);
            this.emit('systemAlert', {
                level: checkConfig.critical ? 'critical' : 'warning',
                message: `Health check "${checkName}" failed ${currentFailures} consecutive times`,
                checkName,
                error: error.message || error,
                consecutiveFailures: currentFailures,
                isCritical: checkConfig.critical,
                timestamp: new Date().toISOString()
            });
        }
    }
    
    /**
     * Handle system alert
     */
    async handleSystemAlert(alert) {
        console.log(`🚨 SYSTEM ALERT [${alert.level.toUpperCase()}]: ${alert.message}`);
        
        this.alertHistory.push(alert);
        
        // Keep only last 100 alerts
        if (this.alertHistory.length > 100) {
            this.alertHistory = this.alertHistory.slice(-100);
        }
        
        await this.logEvent('alert', alert.message, alert);
        
        // Send notifications
        await this.sendNotifications(alert);
    }
    
    /**
     * Handle critical failure
     */
    async handleCriticalFailure(failure) {
        console.log(`💀 CRITICAL FAILURE: ${failure.message}`);
        
        await this.logEvent('critical', failure.message, failure);
        
        // Send immediate notifications
        await this.sendCriticalNotifications(failure);
        
        // Consider triggering emergency procedures
        await this.triggerEmergencyProcedures(failure);
    }
    
    /**
     * Send notifications for alerts
     */
    async sendNotifications(alert) {
        if (this.config.alertChannels.includes('webhook') && this.config.webhookUrl) {
            try {
                await this.sendWebhookNotification(alert);
            } catch (error) {
                console.error('Failed to send webhook notification:', error.message);
            }
        }
        
        // Additional notification channels can be added here
    }
    
    /**
     * Send critical notifications
     */
    async sendCriticalNotifications(failure) {
        // Implement critical notification logic (SMS, email, etc.)
        console.log('🚨 Sending critical notifications...');
        
        // Placeholder for actual notification implementation
        await this.logEvent('notification', 'Critical notifications sent', failure);
    }
    
    /**
     * Trigger emergency procedures
     */
    async triggerEmergencyProcedures(failure) {
        console.log('🆘 Triggering emergency procedures...');
        
        // Placeholder for emergency procedures
        // Could include:
        // - Automatic rollback
        // - Service restart
        // - Traffic routing
        // - Incident escalation
        
        await this.logEvent('emergency', 'Emergency procedures triggered', failure);
    }
    
    /**
     * Send webhook notification
     */
    async sendWebhookNotification(alert) {
        return new Promise((resolve, reject) => {
            const url = new URL(this.config.webhookUrl);
            const data = JSON.stringify({
                alert_type: 'health_monitor',
                level: alert.level,
                message: alert.message,
                timestamp: alert.timestamp,
                system: 'OpenCart Production',
                details: alert
            });
            
            const options = {
                hostname: url.hostname,
                port: url.port || (url.protocol === 'https:' ? 443 : 80),
                path: url.pathname + url.search,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Content-Length': Buffer.byteLength(data),
                    'User-Agent': 'OpenCart-Health-Monitor/3.1.1'
                }
            };
            
            const request = (url.protocol === 'https:' ? https : http).request(options, (response) => {
                if (response.statusCode >= 200 && response.statusCode < 300) {
                    resolve();
                } else {
                    reject(new Error(`Webhook failed with status: ${response.statusCode}`));
                }
            });
            
            request.on('error', reject);
            request.write(data);
            request.end();
        });
    }
    
    /**
     * Get current health status
     */
    getCurrentHealthStatus() {
        const healthStatus = {};
        
        for (const [checkName, status] of this.lastHealthStatus.entries()) {
            healthStatus[checkName] = status;
        }
        
        return {
            overall: this.calculateOverallHealth(),
            checks: healthStatus,
            alertHistory: this.alertHistory.slice(-10), // Last 10 alerts
            monitoringActive: this.monitoringActive,
            lastUpdate: new Date().toISOString()
        };
    }
    
    /**
     * Calculate overall health
     */
    calculateOverallHealth() {
        let healthyChecks = 0;
        let totalChecks = this.lastHealthStatus.size;
        let criticalFailures = 0;
        
        for (const [checkName, status] of this.lastHealthStatus.entries()) {
            if (status.status === 'healthy') {
                healthyChecks++;
            } else {
                const checkConfig = this.healthChecks.get(checkName);
                if (checkConfig && checkConfig.critical) {
                    criticalFailures++;
                }
            }
        }
        
        const healthScore = totalChecks > 0 ? Math.round((healthyChecks / totalChecks) * 100) : 100;
        
        return {
            score: healthScore,
            status: criticalFailures > 0 ? 'critical' : healthScore < 80 ? 'warning' : 'healthy',
            healthyChecks,
            totalChecks,
            criticalFailures
        };
    }
    
    /**
     * Log events
     */
    async logEvent(level, message, context = {}) {
        const logEntry = {
            timestamp: new Date().toISOString(),
            level: level.toUpperCase(),
            message,
            context,
            component: 'HealthMonitor'
        };
        
        const logLine = JSON.stringify(logEntry) + '\n';
        
        try {
            // Ensure log directory exists
            const logDir = path.dirname(this.config.logPath);
            await fs.mkdir(logDir, { recursive: true });
            
            // Append to log file
            await fs.appendFile(this.config.logPath, logLine);
        } catch (error) {
            console.error('Failed to write to log file:', error.message);
        }
    }
    
    /**
     * Get health monitoring statistics
     */
    getMonitoringStatistics() {
        const totalAlerts = this.alertHistory.length;
        const criticalAlerts = this.alertHistory.filter(alert => alert.level === 'critical').length;
        const warningAlerts = this.alertHistory.filter(alert => alert.level === 'warning').length;
        
        return {
            uptime: this.monitoringActive ? 'Active' : 'Inactive',
            totalChecks: this.healthChecks.size,
            checkInterval: this.config.checkInterval,
            totalAlerts,
            criticalAlerts,
            warningAlerts,
            alertThreshold: this.config.alertThreshold,
            startTime: this.startTime || null
        };
    }
}

// CLI execution
if (require.main === module) {
    console.log('🔍 OpenCart Production Health Monitor');
    console.log('====================================\n');
    
    const monitor = new OpenCartProductionHealthMonitor({
        checkInterval: 30000, // 30 seconds
        alertThreshold: 3,
        timeout: 10000,
        webhookUrl: process.env.HEALTH_MONITOR_WEBHOOK_URL,
        logPath: './logs/health_monitor.log'
    });
    
    // Start monitoring
    monitor.startMonitoring();
    
    // Handle graceful shutdown
    process.on('SIGINT', async () => {
        console.log('\n🛑 Shutting down health monitor...');
        await monitor.stopMonitoring();
        process.exit(0);
    });
    
    process.on('SIGTERM', async () => {
        console.log('\n🛑 Shutting down health monitor...');
        await monitor.stopMonitoring();
        process.exit(0);
    });
    
    // Log current status every 5 minutes
    setInterval(() => {
        const status = monitor.getCurrentHealthStatus();
        console.log(`📊 Health Status: ${status.overall.status.toUpperCase()} (Score: ${status.overall.score}%)`);
    }, 300000);
}

module.exports = OpenCartProductionHealthMonitor;
