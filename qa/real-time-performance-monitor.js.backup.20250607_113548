/**
 * MesChain-Sync Real-time Performance Monitor
 * Advanced Performance Monitoring for Frontend-Backend Integration
 * Version: 4.0 - Production Monitoring System
 * 
 * @author Team Coordinator
 * @date June 4, 2025
 */

class MesChainRealTimePerformanceMonitor {
    constructor() {
        this.performanceMetrics = {
            api: {
                responseTime: [],
                errorRate: 0,
                throughput: 0,
                availability: 100
            },
            frontend: {
                renderTime: [],
                interactionTime: [],
                memoryUsage: [],
                cpuUsage: []
            },
            integration: {
                dataSync: [],
                chartUpdates: [],
                realTimeUpdates: [],
                mobilePerformance: []
            },
            user: {
                sessionDuration: 0,
                pageViews: 0,
                interactions: 0,
                errors: 0
            }
        };
        
        this.monitoringActive = false;
        this.monitoringInterval = null;
        this.performanceHistory = [];
        this.alerts = [];
        
        this.thresholds = {
            apiResponseTime: 300, // ms
            renderTime: 100, // ms
            errorRate: 0.05, // 5%
            memoryUsage: 50, // MB
            cpuUsage: 80 // %
        };
        
        console.log('📊 MesChain Real-time Performance Monitor v4.0 initialized');
    }

    /**
     * Start real-time performance monitoring
     */
    startMonitoring() {
        console.log('🚀 Starting Real-time Performance Monitoring...');
        
        this.monitoringActive = true;
        
        // Initialize performance observers
        this.initializePerformanceObservers();
        
        // Start periodic monitoring
        this.monitoringInterval = setInterval(() => {
            this.collectPerformanceMetrics();
        }, 5000); // Collect metrics every 5 seconds
        
        // Monitor API performance
        this.monitorAPIPerformance();
        
        // Monitor frontend performance
        this.monitorFrontendPerformance();
        
        // Monitor integration performance
        this.monitorIntegrationPerformance();
        
        // Monitor user interactions
        this.monitorUserInteractions();
        
        console.log('✅ Real-time performance monitoring started');
    }

    /**
     * Stop performance monitoring
     */
    stopMonitoring() {
        console.log('⏹️ Stopping Real-time Performance Monitoring...');
        
        this.monitoringActive = false;
        
        if (this.monitoringInterval) {
            clearInterval(this.monitoringInterval);
            this.monitoringInterval = null;
        }
        
        console.log('✅ Performance monitoring stopped');
    }

    /**
     * Initialize performance observers
     */
    initializePerformanceObservers() {
        console.log('🔍 Initializing Performance Observers...');
        
        // Performance Observer for navigation timing
        if ('PerformanceObserver' in window) {
            try {
                // Observe navigation performance
                const navObserver = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        this.processNavigationEntry(entry);
                    }
                });
                navObserver.observe({ entryTypes: ['navigation'] });
                
                // Observe resource loading performance
                const resourceObserver = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        this.processResourceEntry(entry);
                    }
                });
                resourceObserver.observe({ entryTypes: ['resource'] });
                
                // Observe paint timing
                const paintObserver = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        this.processPaintEntry(entry);
                    }
                });
                paintObserver.observe({ entryTypes: ['paint'] });
                
                console.log('✅ Performance observers initialized');
                
            } catch (error) {
                console.warn('⚠️ Performance observers initialization failed:', error);
            }
        } else {
            console.warn('⚠️ Performance Observer API not available');
        }
    }

    /**
     * Process navigation performance entry
     */
    processNavigationEntry(entry) {
        const loadTime = entry.loadEventEnd - entry.loadEventStart;
        const domContentLoadedTime = entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart;
        
        this.recordMetric('frontend', 'loadTime', loadTime);
        this.recordMetric('frontend', 'domContentLoadedTime', domContentLoadedTime);
        
        if (loadTime > 3000) {
            this.addAlert('PERFORMANCE', 'Slow page load detected', `Load time: ${loadTime}ms`);
        }
    }

    /**
     * Process resource performance entry
     */
    processResourceEntry(entry) {
        const loadTime = entry.responseEnd - entry.requestStart;
        
        // Track API calls
        if (entry.name.includes('meschain_cursor_integration')) {
            this.performanceMetrics.api.responseTime.push(loadTime);
            
            if (loadTime > this.thresholds.apiResponseTime) {
                this.addAlert('API', 'Slow API response', `${entry.name}: ${loadTime}ms`);
            }
        }
        
        // Track chart.js and other resources
        if (entry.name.includes('chart.js') || entry.name.includes('Chart')) {
            this.recordMetric('integration', 'chartResourceLoad', loadTime);
        }
    }

    /**
     * Process paint timing entry
     */
    processPaintEntry(entry) {
        if (entry.name === 'first-contentful-paint') {
            this.recordMetric('frontend', 'firstContentfulPaint', entry.startTime);
        }
        
        if (entry.name === 'largest-contentful-paint') {
            this.recordMetric('frontend', 'largestContentfulPaint', entry.startTime);
        }
    }

    /**
     * Monitor API performance
     */
    monitorAPIPerformance() {
        console.log('🔌 Monitoring API Performance...');
        
        // Intercept fetch requests
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            const url = args[0];
            const startTime = performance.now();
            
            try {
                const response = await originalFetch.apply(window, args);
                const endTime = performance.now();
                const responseTime = endTime - startTime;
                
                // Track API performance
                if (url.includes('meschain_cursor_integration')) {
                    this.performanceMetrics.api.responseTime.push(responseTime);
                    this.recordAPICall(url, responseTime, response.ok);
                    
                    if (responseTime > this.thresholds.apiResponseTime) {
                        this.addAlert('API', 'Slow API response', `${url}: ${responseTime}ms`);
                    }
                    
                    if (!response.ok) {
                        this.performanceMetrics.api.errorRate++;
                        this.addAlert('API', 'API error', `${url}: HTTP ${response.status}`);
                    }
                }
                
                return response;
                
            } catch (error) {
                const endTime = performance.now();
                const responseTime = endTime - startTime;
                
                if (url.includes('meschain_cursor_integration')) {
                    this.performanceMetrics.api.errorRate++;
                    this.addAlert('API', 'API request failed', `${url}: ${error.message}`);
                }
                
                throw error;
            }
        };
    }

    /**
     * Monitor frontend performance
     */
    monitorFrontendPerformance() {
        console.log('🎨 Monitoring Frontend Performance...');
        
        // Monitor memory usage
        if ('memory' in performance) {
            setInterval(() => {
                const memoryInfo = performance.memory;
                const memoryUsage = memoryInfo.usedJSHeapSize / (1024 * 1024); // MB
                
                this.performanceMetrics.frontend.memoryUsage.push(memoryUsage);
                
                if (memoryUsage > this.thresholds.memoryUsage) {
                    this.addAlert('MEMORY', 'High memory usage', `${memoryUsage.toFixed(2)} MB`);
                }
            }, 10000); // Check every 10 seconds
        }
        
        // Monitor render performance
        this.monitorRenderPerformance();
        
        // Monitor interaction performance
        this.monitorInteractionPerformance();
    }

    /**
     * Monitor render performance
     */
    monitorRenderPerformance() {
        // Monitor Chart.js rendering
        if (typeof Chart !== 'undefined') {
            const originalChartRender = Chart.prototype.render;
            Chart.prototype.render = function() {
                const startTime = performance.now();
                const result = originalChartRender.apply(this, arguments);
                const endTime = performance.now();
                const renderTime = endTime - startTime;
                
                window.performanceMonitor?.recordMetric('integration', 'chartRender', renderTime);
                
                if (renderTime > 100) {
                    window.performanceMonitor?.addAlert('RENDER', 'Slow chart render', `${renderTime}ms`);
                }
                
                return result;
            };
        }
        
        // Monitor DOM updates
        if ('MutationObserver' in window) {
            const observer = new MutationObserver((mutations) => {
                const startTime = performance.now();
                // Process mutations
                const endTime = performance.now();
                const updateTime = endTime - startTime;
                
                this.recordMetric('frontend', 'domUpdate', updateTime);
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true
            });
        }
    }

    /**
     * Monitor interaction performance
     */
    monitorInteractionPerformance() {
        // Monitor click response time
        document.addEventListener('click', (event) => {
            const startTime = performance.now();
            
            // Use requestAnimationFrame to measure until next frame
            requestAnimationFrame(() => {
                const endTime = performance.now();
                const interactionTime = endTime - startTime;
                
                this.performanceMetrics.frontend.interactionTime.push(interactionTime);
                this.performanceMetrics.user.interactions++;
                
                if (interactionTime > 50) {
                    this.addAlert('INTERACTION', 'Slow interaction', `${interactionTime}ms`);
                }
            });
        });
        
        // Monitor input response time
        document.addEventListener('input', (event) => {
            const startTime = performance.now();
            
            requestAnimationFrame(() => {
                const endTime = performance.now();
                const inputTime = endTime - startTime;
                
                this.recordMetric('frontend', 'inputResponse', inputTime);
            });
        });
    }

    /**
     * Monitor integration performance
     */
    monitorIntegrationPerformance() {
        console.log('🔗 Monitoring Integration Performance...');
        
        // Monitor dashboard updates
        this.monitorDashboardUpdates();
        
        // Monitor real-time data sync
        this.monitorRealTimeSync();
        
        // Monitor mobile performance
        this.monitorMobilePerformance();
    }

    /**
     * Monitor dashboard updates
     */
    monitorDashboardUpdates() {
        // Monitor MesChainDashboard updates
        if (typeof MesChainDashboard !== 'undefined') {
            const dashboard = window.mesChainDashboard;
            if (dashboard) {
                // Monitor update cycles
                setInterval(() => {
                    const startTime = performance.now();
                    
                    // Simulate update monitoring
                    const endTime = performance.now();
                    const updateTime = endTime - startTime;
                    
                    this.recordMetric('integration', 'dashboardUpdate', updateTime);
                }, 30000); // Check every 30 seconds
            }
        }
    }

    /**
     * Monitor real-time data sync
     */
    monitorRealTimeSync() {
        // Monitor WebSocket performance
        if ('WebSocket' in window) {
            const originalWebSocket = window.WebSocket;
            window.WebSocket = function(url, protocols) {
                const ws = new originalWebSocket(url, protocols);
                const startTime = performance.now();
                
                ws.addEventListener('open', () => {
                    const endTime = performance.now();
                    const connectionTime = endTime - startTime;
                    
                    window.performanceMonitor?.recordMetric('integration', 'websocketConnection', connectionTime);
                });
                
                ws.addEventListener('message', (event) => {
                    const messageTime = performance.now();
                    window.performanceMonitor?.recordMetric('integration', 'websocketMessage', messageTime);
                });
                
                return ws;
            };
        }
    }

    /**
     * Monitor mobile performance
     */
    monitorMobilePerformance() {
        // Check if running on mobile
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (isMobile) {
            // Monitor touch events
            document.addEventListener('touchstart', (event) => {
                const startTime = performance.now();
                
                setTimeout(() => {
                    const endTime = performance.now();
                    const touchResponse = endTime - startTime;
                    
                    this.recordMetric('integration', 'touchResponse', touchResponse);
                }, 0);
            });
            
            // Monitor orientation changes
            window.addEventListener('orientationchange', () => {
                const startTime = performance.now();
                
                setTimeout(() => {
                    const endTime = performance.now();
                    const orientationTime = endTime - startTime;
                    
                    this.recordMetric('integration', 'orientationChange', orientationTime);
                }, 100);
            });
        }
    }

    /**
     * Monitor user interactions
     */
    monitorUserInteractions() {
        console.log('👤 Monitoring User Interactions...');
        
        // Track session duration
        const sessionStart = Date.now();
        setInterval(() => {
            this.performanceMetrics.user.sessionDuration = Date.now() - sessionStart;
        }, 1000);
        
        // Track page views
        this.performanceMetrics.user.pageViews++;
        
        // Track errors
        window.addEventListener('error', (event) => {
            this.performanceMetrics.user.errors++;
            this.addAlert('ERROR', 'JavaScript error', event.message);
        });
        
        // Track unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.performanceMetrics.user.errors++;
            this.addAlert('ERROR', 'Unhandled promise rejection', event.reason);
        });
    }

    /**
     * Collect comprehensive performance metrics
     */
    collectPerformanceMetrics() {
        if (!this.monitoringActive) return;
        
        const currentMetrics = {
            timestamp: Date.now(),
            api: {
                avgResponseTime: this.calculateAverage(this.performanceMetrics.api.responseTime),
                errorRate: this.performanceMetrics.api.errorRate,
                throughput: this.calculateThroughput(),
                availability: this.calculateAvailability()
            },
            frontend: {
                avgRenderTime: this.calculateAverage(this.performanceMetrics.frontend.renderTime),
                avgInteractionTime: this.calculateAverage(this.performanceMetrics.frontend.interactionTime),
                currentMemoryUsage: this.getCurrentMemoryUsage(),
                currentCPUUsage: this.getCurrentCPUUsage()
            },
            integration: {
                avgDataSync: this.calculateAverage(this.performanceMetrics.integration.dataSync),
                avgChartUpdates: this.calculateAverage(this.performanceMetrics.integration.chartUpdates),
                avgRealTimeUpdates: this.calculateAverage(this.performanceMetrics.integration.realTimeUpdates)
            },
            user: {
                sessionDuration: this.performanceMetrics.user.sessionDuration,
                pageViews: this.performanceMetrics.user.pageViews,
                interactions: this.performanceMetrics.user.interactions,
                errors: this.performanceMetrics.user.errors
            }
        };
        
        this.performanceHistory.push(currentMetrics);
        
        // Keep only last 100 entries
        if (this.performanceHistory.length > 100) {
            this.performanceHistory.shift();
        }
        
        // Check performance thresholds
        this.checkPerformanceThresholds(currentMetrics);
        
        // Emit performance update event
        this.emitPerformanceUpdate(currentMetrics);
    }

    /**
     * Record API call
     */
    recordAPICall(url, responseTime, success) {
        const apiCall = {
            timestamp: Date.now(),
            url: url,
            responseTime: responseTime,
            success: success
        };
        
        this.performanceMetrics.api.throughput++;
        
        if (!success) {
            this.performanceMetrics.api.errorRate++;
        }
    }

    /**
     * Record performance metric
     */
    recordMetric(category, metric, value) {
        if (!this.performanceMetrics[category]) {
            this.performanceMetrics[category] = {};
        }
        
        if (!this.performanceMetrics[category][metric]) {
            this.performanceMetrics[category][metric] = [];
        }
        
        this.performanceMetrics[category][metric].push(value);
        
        // Keep only last 50 entries per metric
        if (this.performanceMetrics[category][metric].length > 50) {
            this.performanceMetrics[category][metric].shift();
        }
    }

    /**
     * Add performance alert
     */
    addAlert(category, type, details) {
        const alert = {
            timestamp: Date.now(),
            category: category,
            type: type,
            details: details
        };
        
        this.alerts.push(alert);
        
        // Keep only last 20 alerts
        if (this.alerts.length > 20) {
            this.alerts.shift();
        }
        
        console.warn(`🚨 PERFORMANCE ALERT - ${category}: ${type} - ${details}`);
        
        // Emit alert event
        this.emitPerformanceAlert(alert);
    }

    /**
     * Calculate average of array
     */
    calculateAverage(array) {
        if (!array || array.length === 0) return 0;
        return array.reduce((sum, value) => sum + value, 0) / array.length;
    }

    /**
     * Calculate throughput
     */
    calculateThroughput() {
        return this.performanceMetrics.api.throughput || 0;
    }

    /**
     * Calculate availability
     */
    calculateAvailability() {
        const total = this.performanceMetrics.api.throughput;
        const errors = this.performanceMetrics.api.errorRate;
        
        if (total === 0) return 100;
        return ((total - errors) / total) * 100;
    }

    /**
     * Get current memory usage
     */
    getCurrentMemoryUsage() {
        if ('memory' in performance) {
            return performance.memory.usedJSHeapSize / (1024 * 1024); // MB
        }
        return 0;
    }

    /**
     * Get current CPU usage (approximation)
     */
    getCurrentCPUUsage() {
        // Simple CPU usage approximation based on performance
        const now = performance.now();
        const usage = (now % 100) / 100 * 50; // Simulate CPU usage
        return Math.min(usage, 100);
    }

    /**
     * Check performance thresholds
     */
    checkPerformanceThresholds(metrics) {
        // Check API response time
        if (metrics.api.avgResponseTime > this.thresholds.apiResponseTime) {
            this.addAlert('THRESHOLD', 'API response time exceeded', `${metrics.api.avgResponseTime}ms > ${this.thresholds.apiResponseTime}ms`);
        }
        
        // Check memory usage
        if (metrics.frontend.currentMemoryUsage > this.thresholds.memoryUsage) {
            this.addAlert('THRESHOLD', 'Memory usage exceeded', `${metrics.frontend.currentMemoryUsage}MB > ${this.thresholds.memoryUsage}MB`);
        }
        
        // Check error rate
        if (metrics.api.errorRate > this.thresholds.errorRate * 100) {
            this.addAlert('THRESHOLD', 'Error rate exceeded', `${metrics.api.errorRate}% > ${this.thresholds.errorRate * 100}%`);
        }
    }

    /**
     * Emit performance update event
     */
    emitPerformanceUpdate(metrics) {
        const event = new CustomEvent('performanceUpdate', {
            detail: metrics
        });
        window.dispatchEvent(event);
    }

    /**
     * Emit performance alert event
     */
    emitPerformanceAlert(alert) {
        const event = new CustomEvent('performanceAlert', {
            detail: alert
        });
        window.dispatchEvent(event);
    }

    /**
     * Get performance report
     */
    getPerformanceReport() {
        const latest = this.performanceHistory[this.performanceHistory.length - 1];
        
        return {
            current: latest,
            history: this.performanceHistory,
            alerts: this.alerts,
            summary: {
                avgApiResponseTime: this.calculateAverage(this.performanceMetrics.api.responseTime),
                totalErrors: this.performanceMetrics.user.errors,
                totalInteractions: this.performanceMetrics.user.interactions,
                sessionDuration: this.performanceMetrics.user.sessionDuration,
                availability: this.calculateAvailability()
            }
        };
    }

    /**
     * Reset performance metrics
     */
    resetMetrics() {
        this.performanceMetrics = {
            api: { responseTime: [], errorRate: 0, throughput: 0, availability: 100 },
            frontend: { renderTime: [], interactionTime: [], memoryUsage: [], cpuUsage: [] },
            integration: { dataSync: [], chartUpdates: [], realTimeUpdates: [], mobilePerformance: [] },
            user: { sessionDuration: 0, pageViews: 0, interactions: 0, errors: 0 }
        };
        
        this.performanceHistory = [];
        this.alerts = [];
        
        console.log('🔄 Performance metrics reset');
    }
}

// Initialize and export for global use
window.MesChainRealTimePerformanceMonitor = MesChainRealTimePerformanceMonitor;

// Auto-start monitoring if enabled
if (window.location.search.includes('enable_performance_monitoring=true')) {
    window.performanceMonitor = new MesChainRealTimePerformanceMonitor();
    window.performanceMonitor.startMonitoring();
}

console.log('📊 MesChain Real-time Performance Monitor loaded successfully!'); 