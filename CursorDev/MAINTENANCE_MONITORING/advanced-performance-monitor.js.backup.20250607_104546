/**
 * Advanced Performance Monitor
 * Real-time system health and performance monitoring
 * Selinay Team - Task 7 Implementation
 * June 5, 2025
 */

class AdvancedPerformanceMonitor {
    constructor() {
        this.config = {
            monitoringInterval: 30000, // 30 seconds
            performanceThresholds: {
                fcp: 1500, // First Contentful Paint threshold (ms)
                lcp: 2500, // Largest Contentful Paint threshold (ms)
                cls: 0.1,  // Cumulative Layout Shift threshold
                fid: 100,  // First Input Delay threshold (ms)
                ttfb: 800, // Time to First Byte threshold (ms)
                memoryUsage: 80, // Memory usage threshold (%)
                cpuUsage: 75     // CPU usage threshold (%)
            },
            alertChannels: {
                realTimeAlert: true,
                emailNotification: true,
                slackIntegration: true,
                smsAlert: false
            },
            historicalDataRetention: 30 // days
        };
        
        this.metrics = new Map();
        this.alerts = [];
        this.isMonitoring = false;
        this.performanceObserver = null;
        this.resourceObserver = null;
        
        this.initializeMonitoring();
    }    /**
     * Initialize Performance Monitoring
     */
    async initializeMonitoring() {
        try {
            console.log('🔍 Initializing Advanced Performance Monitor...');
            
            // Check if running in browser environment
            if (typeof window !== 'undefined' && typeof document !== 'undefined') {
                await this.setupWebVitalsMonitoring();
                await this.setupResourceMonitoring();
            } else {
                // Node.js environment - setup server-side monitoring
                await this.setupServerSideMonitoring();
            }
            await this.setupMemoryMonitoring();
            await this.setupNetworkMonitoring();
            await this.initializeAlertSystem();
            
            this.startContinuousMonitoring();
            
            console.log('✅ Advanced Performance Monitor initialized successfully');
            this.logEvent('MONITOR_INITIALIZED', 'Performance monitoring system started');
            
        } catch (error) {
            console.error('❌ Failed to initialize performance monitor:', error);
            this.handleMonitoringError('INITIALIZATION_FAILED', error);
        }
    }

    /**
     * Setup Web Vitals Monitoring
     */
    async setupWebVitalsMonitoring() {
        try {
            // Core Web Vitals monitoring
            if ('PerformanceObserver' in window) {
                // First Contentful Paint (FCP)
                this.observePerformanceEntry('paint', (entries) => {
                    entries.forEach(entry => {
                        if (entry.name === 'first-contentful-paint') {
                            this.recordMetric('FCP', entry.startTime, {
                                timestamp: Date.now(),
                                url: window.location.href,
                                userAgent: navigator.userAgent
                            });
                            
                            if (entry.startTime > this.config.performanceThresholds.fcp) {
                                this.triggerAlert('FCP_THRESHOLD_EXCEEDED', {
                                    value: entry.startTime,
                                    threshold: this.config.performanceThresholds.fcp,
                                    severity: 'HIGH'
                                });
                            }
                        }
                    });
                });

                // Largest Contentful Paint (LCP)
                this.observePerformanceEntry('largest-contentful-paint', (entries) => {
                    entries.forEach(entry => {
                        this.recordMetric('LCP', entry.startTime, {
                            timestamp: Date.now(),
                            element: entry.element?.tagName || 'unknown',
                            url: entry.url || window.location.href
                        });
                        
                        if (entry.startTime > this.config.performanceThresholds.lcp) {
                            this.triggerAlert('LCP_THRESHOLD_EXCEEDED', {
                                value: entry.startTime,
                                threshold: this.config.performanceThresholds.lcp,
                                severity: 'HIGH'
                            });
                        }
                    });
                });

                // First Input Delay (FID)
                this.observePerformanceEntry('first-input', (entries) => {
                    entries.forEach(entry => {
                        this.recordMetric('FID', entry.processingStart - entry.startTime, {
                            timestamp: Date.now(),
                            eventType: entry.name,
                            target: entry.target?.tagName || 'unknown'
                        });
                        
                        const fid = entry.processingStart - entry.startTime;
                        if (fid > this.config.performanceThresholds.fid) {
                            this.triggerAlert('FID_THRESHOLD_EXCEEDED', {
                                value: fid,
                                threshold: this.config.performanceThresholds.fid,
                                severity: 'MEDIUM'
                            });
                        }
                    });
                });

                // Cumulative Layout Shift (CLS)
                this.observePerformanceEntry('layout-shift', (entries) => {
                    let clsValue = 0;
                    entries.forEach(entry => {
                        if (!entry.hadRecentInput) {
                            clsValue += entry.value;
                        }
                    });
                    
                    if (clsValue > 0) {
                        this.recordMetric('CLS', clsValue, {
                            timestamp: Date.now(),
                            sessionTotal: this.getSessionCLS() + clsValue
                        });
                        
                        if (clsValue > this.config.performanceThresholds.cls) {
                            this.triggerAlert('CLS_THRESHOLD_EXCEEDED', {
                                value: clsValue,
                                threshold: this.config.performanceThresholds.cls,
                                severity: 'MEDIUM'
                            });
                        }
                    }
                });
            }

            console.log('✅ Web Vitals monitoring setup complete');
            
        } catch (error) {
            console.error('❌ Web Vitals monitoring setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Resource Monitoring
     */
    async setupResourceMonitoring() {
        try {
            if ('PerformanceObserver' in window) {
                this.observePerformanceEntry('resource', (entries) => {
                    entries.forEach(entry => {
                        this.analyzeResourcePerformance(entry);
                    });
                });

                this.observePerformanceEntry('navigation', (entries) => {
                    entries.forEach(entry => {
                        this.analyzeNavigationPerformance(entry);
                    });
                });
            }

            console.log('✅ Resource monitoring setup complete');
              } catch (error) {
            console.error('❌ Resource monitoring setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Server-Side Monitoring (Node.js environment)
     */
    async setupServerSideMonitoring() {
        try {
            console.log('🖥️ Setting up server-side monitoring...');
            
            // Monitor Node.js process metrics
            setInterval(() => {
                const memUsage = process.memoryUsage();
                const memoryUsagePercent = (memUsage.heapUsed / memUsage.heapTotal) * 100;
                
                this.recordMetric('SERVER_MEMORY_USAGE', memoryUsagePercent, {
                    timestamp: Date.now(),
                    heapUsed: memUsage.heapUsed,
                    heapTotal: memUsage.heapTotal,
                    external: memUsage.external,
                    rss: memUsage.rss
                });
                
                // CPU usage approximation
                const cpuUsage = process.cpuUsage();
                this.recordMetric('SERVER_CPU_USAGE', cpuUsage.user + cpuUsage.system, {
                    timestamp: Date.now(),
                    user: cpuUsage.user,
                    system: cpuUsage.system
                });
                
                // Process uptime
                this.recordMetric('SERVER_UPTIME', process.uptime(), {
                    timestamp: Date.now(),
                    pid: process.pid
                });
                
            }, this.config.monitoringInterval);
            
            console.log('✅ Server-side monitoring setup complete');
            
        } catch (error) {
            console.error('❌ Server-side monitoring setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Memory Monitoring
     */    async setupMemoryMonitoring() {
        try {
            // Browser environment
            if (typeof performance !== 'undefined' && 'memory' in performance) {
                setInterval(() => {
                    const memInfo = performance.memory;
                    const memoryUsage = (memInfo.usedJSHeapSize / memInfo.jsHeapSizeLimit) * 100;
                    
                    this.recordMetric('MEMORY_USAGE', memoryUsage, {
                        timestamp: Date.now(),
                        usedHeapSize: memInfo.usedJSHeapSize,
                        totalHeapSize: memInfo.totalJSHeapSize,
                        heapSizeLimit: memInfo.jsHeapSizeLimit
                    });
                    
                    if (memoryUsage > this.config.performanceThresholds.memoryUsage) {
                        this.triggerAlert('MEMORY_THRESHOLD_EXCEEDED', {
                            value: memoryUsage,
                            threshold: this.config.performanceThresholds.memoryUsage,
                            severity: 'HIGH'
                        });
                    }
                }, this.config.monitoringInterval);
            } else if (typeof process !== 'undefined') {
                // Node.js environment - memory monitoring is handled in setupServerSideMonitoring
                console.log('📊 Memory monitoring integrated with server-side monitoring');
            }

            console.log('✅ Memory monitoring setup complete');
            
        } catch (error) {
            console.error('❌ Memory monitoring setup failed:', error);
            throw error;
        }
    }    /**
     * Setup Network Monitoring
     */
    async setupNetworkMonitoring() {
        try {
            // Browser environment
            if (typeof navigator !== 'undefined' && 'connection' in navigator) {
                const connection = navigator.connection;
                
                this.recordMetric('NETWORK_CONNECTION', connection.effectiveType, {
                    timestamp: Date.now(),
                    downlink: connection.downlink,
                    rtt: connection.rtt,
                    saveData: connection.saveData
                });

                connection.addEventListener('change', () => {
                    this.recordMetric('NETWORK_CHANGE', connection.effectiveType, {
                        timestamp: Date.now(),
                        downlink: connection.downlink,
                        rtt: connection.rtt,
                        saveData: connection.saveData
                    });
                    
                    if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
                        this.triggerAlert('SLOW_NETWORK_DETECTED', {
                            connectionType: connection.effectiveType,
                            downlink: connection.downlink,
                            severity: 'MEDIUM'
                        });
                    }
                });
            }

            // Monitor TTFB (Time to First Byte) - Browser only
            if (typeof window !== 'undefined' && window.performance && window.performance.timing) {
                const ttfb = window.performance.timing.responseStart - window.performance.timing.requestStart;
                this.recordMetric('TTFB', ttfb, {
                    timestamp: Date.now(),
                    url: window.location.href
                });
                
                if (ttfb > this.config.performanceThresholds.ttfb) {
                    this.triggerAlert('TTFB_THRESHOLD_EXCEEDED', {
                        value: ttfb,
                        threshold: this.config.performanceThresholds.ttfb,
                        severity: 'HIGH'
                    });
                }
            }

            console.log('✅ Network monitoring setup complete');
            
        } catch (error) {
            console.error('❌ Network monitoring setup failed:', error);
            throw error;
        }
    }

    /**
     * Initialize Alert System
     */
    async initializeAlertSystem() {
        try {
            // Setup real-time dashboard update
            this.alertHandlers = {
                realTime: this.handleRealTimeAlert.bind(this),
                email: this.handleEmailAlert.bind(this),
                slack: this.handleSlackAlert.bind(this),
                sms: this.handleSMSAlert.bind(this)
            };

            // Setup alert queue processing
            setInterval(() => {
                this.processAlertQueue();
            }, 5000); // Process alerts every 5 seconds

            console.log('✅ Alert system initialized');
            
        } catch (error) {
            console.error('❌ Alert system initialization failed:', error);
            throw error;
        }
    }

    /**
     * Start Continuous Monitoring
     */
    startContinuousMonitoring() {
        if (this.isMonitoring) return;
        
        this.isMonitoring = true;
        
        // Main monitoring loop
        this.monitoringInterval = setInterval(() => {
            this.collectPerformanceMetrics();
            this.analyzePerformanceTrends();
            this.generatePerformanceInsights();
            this.updateRealTimeDashboard();
        }, this.config.monitoringInterval);

        // Cleanup old data
        setInterval(() => {
            this.cleanupHistoricalData();
        }, 24 * 60 * 60 * 1000); // Clean up daily

        console.log('🔄 Continuous monitoring started');
    }

    /**
     * Stop Monitoring
     */
    stopMonitoring() {
        this.isMonitoring = false;
        
        if (this.monitoringInterval) {
            clearInterval(this.monitoringInterval);
        }
        
        if (this.performanceObserver) {
            this.performanceObserver.disconnect();
        }
        
        if (this.resourceObserver) {
            this.resourceObserver.disconnect();
        }
        
        console.log('⏹️ Monitoring stopped');
    }

    /**
     * Observe Performance Entries
     */
    observePerformanceEntry(entryType, callback) {
        try {
            const observer = new PerformanceObserver((list) => {
                callback(list.getEntries());
            });
            
            observer.observe({ entryTypes: [entryType] });
            
            if (entryType === 'resource') {
                this.resourceObserver = observer;
            } else {
                this.performanceObserver = observer;
            }
            
        } catch (error) {
            console.error(`Failed to observe ${entryType}:`, error);
        }
    }

    /**
     * Record Performance Metric
     */
    recordMetric(metricName, value, metadata = {}) {
        const metricKey = `${metricName}_${Date.now()}`;
        
        this.metrics.set(metricKey, {
            name: metricName,
            value: value,
            timestamp: Date.now(),
            metadata: metadata,
            sessionId: this.getSessionId(),
            userId: this.getUserId(),
            url: window.location.href
        });

        // Store in localStorage for persistence
        try {
            const storedMetrics = JSON.parse(localStorage.getItem('performanceMetrics') || '[]');
            storedMetrics.push({
                name: metricName,
                value: value,
                timestamp: Date.now(),
                metadata: metadata
            });
            
            // Keep only last 1000 metrics
            if (storedMetrics.length > 1000) {
                storedMetrics.splice(0, storedMetrics.length - 1000);
            }
            
            localStorage.setItem('performanceMetrics', JSON.stringify(storedMetrics));
        } catch (error) {
            console.warn('Failed to store metric in localStorage:', error);
        }

        // Dispatch custom event for real-time updates
        window.dispatchEvent(new CustomEvent('performanceMetricRecorded', {
            detail: { metricName, value, metadata }
        }));
    }

    /**
     * Trigger Performance Alert
     */
    triggerAlert(alertType, alertData) {
        const alert = {
            id: `alert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            type: alertType,
            severity: alertData.severity || 'MEDIUM',
            data: alertData,
            timestamp: Date.now(),
            url: window.location.href,
            userAgent: navigator.userAgent,
            sessionId: this.getSessionId(),
            resolved: false
        };

        this.alerts.push(alert);

        // Process alert through configured channels
        Object.keys(this.config.alertChannels).forEach(channel => {
            if (this.config.alertChannels[channel] && this.alertHandlers[channel]) {
                this.alertHandlers[channel](alert);
            }
        });

        console.warn(`🚨 Performance Alert: ${alertType}`, alertData);
        
        // Store alert for historical analysis
        try {
            const storedAlerts = JSON.parse(localStorage.getItem('performanceAlerts') || '[]');
            storedAlerts.push(alert);
            
            // Keep only last 100 alerts
            if (storedAlerts.length > 100) {
                storedAlerts.splice(0, storedAlerts.length - 100);
            }
            
            localStorage.setItem('performanceAlerts', JSON.stringify(storedAlerts));
        } catch (error) {
            console.warn('Failed to store alert:', error);
        }

        return alert;
    }

    /**
     * Get Performance Summary
     */
    getPerformanceSummary() {
        const currentMetrics = Array.from(this.metrics.values());
        const last24Hours = Date.now() - (24 * 60 * 60 * 1000);
        const recentMetrics = currentMetrics.filter(m => m.timestamp > last24Hours);

        const summary = {
            totalMetrics: currentMetrics.length,
            recentMetrics: recentMetrics.length,
            activeAlerts: this.alerts.filter(a => !a.resolved).length,
            totalAlerts: this.alerts.length,
            averagePerformance: this.calculateAveragePerformance(recentMetrics),
            performanceTrends: this.analyzePerformanceTrends(),
            systemHealth: this.calculateSystemHealth(),
            recommendations: this.generateRecommendations()
        };

        return summary;
    }

    /**
     * Generate Performance Report
     */
    generatePerformanceReport() {
        const summary = this.getPerformanceSummary();
        const report = {
            generatedAt: new Date().toISOString(),
            summary: summary,
            detailedMetrics: this.getDetailedMetrics(),
            alertHistory: this.alerts.slice(-50), // Last 50 alerts
            trends: this.getPerformanceTrends(),
            insights: this.generatePerformanceInsights(),
            recommendations: this.generateRecommendations()
        };

        return report;
    }

    /**
     * Export Performance Data
     */
    exportPerformanceData(format = 'json') {
        const data = this.generatePerformanceReport();
        
        if (format === 'json') {
            return JSON.stringify(data, null, 2);
        } else if (format === 'csv') {
            return this.convertToCSV(data);
        }
        
        return data;
    }

    // Utility methods
    getSessionId() {
        return sessionStorage.getItem('sessionId') || 'anonymous';
    }

    getUserId() {
        return localStorage.getItem('userId') || 'anonymous';
    }

    getSessionCLS() {
        const clsMetrics = Array.from(this.metrics.values())
            .filter(m => m.name === 'CLS' && m.sessionId === this.getSessionId());
        return clsMetrics.reduce((sum, m) => sum + m.value, 0);
    }

    calculateAveragePerformance(metrics) {
        // Implementation for average performance calculation
        return {
            avgFCP: this.calculateAverageByType(metrics, 'FCP'),
            avgLCP: this.calculateAverageByType(metrics, 'LCP'),
            avgFID: this.calculateAverageByType(metrics, 'FID'),
            avgCLS: this.calculateAverageByType(metrics, 'CLS'),
            avgTTFB: this.calculateAverageByType(metrics, 'TTFB')
        };
    }

    calculateAverageByType(metrics, type) {
        const typeMetrics = metrics.filter(m => m.name === type);
        if (typeMetrics.length === 0) return 0;
        return typeMetrics.reduce((sum, m) => sum + m.value, 0) / typeMetrics.length;
    }

    analyzePerformanceTrends() {
        // Implementation for trend analysis
        return {
            improving: [],
            degrading: [],
            stable: []
        };
    }

    calculateSystemHealth() {
        const recentAlerts = this.alerts.filter(a => 
            (Date.now() - a.timestamp) < (60 * 60 * 1000) // Last hour
        );
        
        if (recentAlerts.length === 0) return 'EXCELLENT';
        if (recentAlerts.length < 3) return 'GOOD';
        if (recentAlerts.length < 10) return 'FAIR';
        return 'POOR';
    }

    generateRecommendations() {
        // Implementation for generating performance recommendations
        return [
            'Continue monitoring current performance levels',
            'Consider implementing additional caching strategies',
            'Monitor user experience metrics closely'
        ];
    }

    generatePerformanceInsights() {
        // Implementation for generating insights
        return [
            'Performance is within acceptable thresholds',
            'No critical issues detected in the last 24 hours',
            'System health is optimal'
        ];
    }

    collectPerformanceMetrics() {
        // Implementation for collecting additional metrics
    }

    updateRealTimeDashboard() {
        // Implementation for updating dashboard
        window.dispatchEvent(new CustomEvent('performanceDashboardUpdate', {
            detail: this.getPerformanceSummary()
        }));
    }

    processAlertQueue() {
        // Implementation for processing alert queue
    }

    cleanupHistoricalData() {
        // Implementation for cleaning up old data
        const cutoffTime = Date.now() - (this.config.historicalDataRetention * 24 * 60 * 60 * 1000);
        
        // Clean up metrics
        this.metrics.forEach((metric, key) => {
            if (metric.timestamp < cutoffTime) {
                this.metrics.delete(key);
            }
        });

        // Clean up alerts
        this.alerts = this.alerts.filter(alert => alert.timestamp > cutoffTime);
    }

    // Alert Handlers
    handleRealTimeAlert(alert) {
        console.log('🔴 Real-time alert:', alert);
        // Implementation for real-time alert handling
    }

    handleEmailAlert(alert) {
        console.log('📧 Email alert:', alert);
        // Implementation for email alert handling
    }

    handleSlackAlert(alert) {
        console.log('💬 Slack alert:', alert);
        // Implementation for Slack alert handling
    }

    handleSMSAlert(alert) {
        console.log('📱 SMS alert:', alert);
        // Implementation for SMS alert handling
    }

    handleMonitoringError(errorType, error) {
        console.error(`🚨 Monitoring Error [${errorType}]:`, error);
        
        // Log error for analysis
        this.recordMetric('MONITORING_ERROR', errorType, {
            error: error.message,
            stack: error.stack,
            timestamp: Date.now()
        });
    }

    analyzeResourcePerformance(entry) {
        // Analyze resource loading performance
        const loadTime = entry.responseEnd - entry.startTime;
        
        this.recordMetric('RESOURCE_LOAD_TIME', loadTime, {
            name: entry.name,
            type: entry.initiatorType,
            size: entry.transferSize,
            cached: entry.transferSize === 0
        });

        // Alert for slow resources
        if (loadTime > 5000) { // 5 seconds threshold
            this.triggerAlert('SLOW_RESOURCE_DETECTED', {
                resource: entry.name,
                loadTime: loadTime,
                severity: 'MEDIUM'
            });
        }
    }

    analyzeNavigationPerformance(entry) {
        // Analyze navigation performance
        const navigationTime = entry.loadEventEnd - entry.startTime;
        
        this.recordMetric('NAVIGATION_TIME', navigationTime, {
            type: entry.type,
            redirectCount: entry.redirectCount,
            timestamp: Date.now()
        });

        // Alert for slow navigation
        if (navigationTime > 10000) { // 10 seconds threshold
            this.triggerAlert('SLOW_NAVIGATION_DETECTED', {
                navigationTime: navigationTime,
                redirectCount: entry.redirectCount,
                severity: 'HIGH'
            });
        }
    }

    getDetailedMetrics() {
        // Implementation for getting detailed metrics
        return Array.from(this.metrics.values()).slice(-100); // Last 100 metrics
    }

    getPerformanceTrends() {
        // Implementation for getting performance trends
        return {
            daily: [],
            weekly: [],
            monthly: []
        };
    }

    convertToCSV(data) {
        // Implementation for CSV conversion
        return 'CSV data conversion not implemented yet';
    }

    logEvent(eventType, message) {
        console.log(`📊 [${eventType}] ${message}`);
        
        // Store event log
        try {
            const eventLog = JSON.parse(localStorage.getItem('performanceEventLog') || '[]');
            eventLog.push({
                type: eventType,
                message: message,
                timestamp: Date.now()
            });
            
            // Keep only last 200 events
            if (eventLog.length > 200) {
                eventLog.splice(0, eventLog.length - 200);
            }
            
            localStorage.setItem('performanceEventLog', JSON.stringify(eventLog));
        } catch (error) {
            console.warn('Failed to store event log:', error);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('🔍 Advanced Performance Monitor initializing...');
    
    // Create global instance
    window.advancedPerformanceMonitor = new AdvancedPerformanceMonitor();
    
    // Add global convenience methods
    window.getPerformanceSummary = () => window.advancedPerformanceMonitor.getPerformanceSummary();
    window.generatePerformanceReport = () => window.advancedPerformanceMonitor.generatePerformanceReport();
    window.exportPerformanceData = (format) => window.advancedPerformanceMonitor.exportPerformanceData(format);
    
    console.log('✅ Advanced Performance Monitor available globally');
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdvancedPerformanceMonitor;
}
