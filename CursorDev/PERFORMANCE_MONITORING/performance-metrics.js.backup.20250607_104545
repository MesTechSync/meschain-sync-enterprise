/**
 * MesChain-Sync Enterprise - Performance Metrics Collector
 * Selinay Team - Frontend UI/UX Specialist Task 6 Implementation
 * Real-time Performance Tracking System
 * Date: June 6, 2025
 */

class PerformanceMetricsCollector {
    constructor(config = {}) {
        this.config = {
            apiEndpoint: config.apiEndpoint || '/api/performance-metrics',
            sampleRate: config.sampleRate || 0.1, // 10% sampling
            batchSize: config.batchSize || 10,
            flushInterval: config.flushInterval || 30000, // 30 seconds
            enableRealTimeAlerts: config.enableRealTimeAlerts || true,
            thresholds: {
                lcp: 2500,        // Largest Contentful Paint
                fid: 100,         // First Input Delay
                cls: 0.1,         // Cumulative Layout Shift
                fcp: 1800,        // First Contentful Paint
                ttfb: 600,        // Time to First Byte
                memoryUsage: 50   // Memory usage percentage
            },
            ...config
        };
        
        this.metricsQueue = [];
        this.observer = null;
        this.memoryObserver = null;
        this.performanceEntries = [];
        this.isInitialized = false;
        
        this.init();
    }

    /**
     * Initialize performance monitoring
     */
    init() {
        if (this.isInitialized) return;
        
        try {
            this.setupWebVitalsMonitoring();
            this.setupNavigationTimingMonitoring();
            this.setupResourceTimingMonitoring();
            this.setupMemoryMonitoring();
            this.setupUserTimingMonitoring();
            this.setupCustomMetrics();
            this.startPeriodicCollection();
            
            this.isInitialized = true;
            console.log('✅ Selinay Performance Metrics Collector initialized');
        } catch (error) {
            console.error('❌ Failed to initialize performance metrics:', error);
        }
    }

    /**
     * Setup Web Vitals monitoring (Core Web Vitals)
     */
    setupWebVitalsMonitoring() {
        // Largest Contentful Paint (LCP)
        if ('PerformanceObserver' in window) {
            const lcpObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const lastEntry = entries[entries.length - 1];
                
                this.recordMetric({
                    name: 'lcp',
                    value: lastEntry.startTime,
                    timestamp: Date.now(),
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    type: 'web-vital'
                });
                
                if (lastEntry.startTime > this.config.thresholds.lcp) {
                    this.triggerAlert('LCP threshold exceeded', lastEntry.startTime);
                }
            });
            
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
        }

        // First Input Delay (FID)
        if ('PerformanceObserver' in window) {
            const fidObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                entries.forEach(entry => {
                    this.recordMetric({
                        name: 'fid',
                        value: entry.processingStart - entry.startTime,
                        timestamp: Date.now(),
                        url: window.location.href,
                        type: 'web-vital'
                    });
                    
                    const fidValue = entry.processingStart - entry.startTime;
                    if (fidValue > this.config.thresholds.fid) {
                        this.triggerAlert('FID threshold exceeded', fidValue);
                    }
                });
            });
            
            fidObserver.observe({ entryTypes: ['first-input'] });
        }

        // Cumulative Layout Shift (CLS)
        if ('PerformanceObserver' in window) {
            let clsValue = 0;
            const clsObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                entries.forEach(entry => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                });
                
                this.recordMetric({
                    name: 'cls',
                    value: clsValue,
                    timestamp: Date.now(),
                    url: window.location.href,
                    type: 'web-vital'
                });
                
                if (clsValue > this.config.thresholds.cls) {
                    this.triggerAlert('CLS threshold exceeded', clsValue);
                }
            });
            
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        }
    }

    /**
     * Setup Navigation Timing monitoring
     */
    setupNavigationTimingMonitoring() {
        if ('performance' in window && 'getEntriesByType' in performance) {
            const navigationEntries = performance.getEntriesByType('navigation');
            
            if (navigationEntries.length > 0) {
                const navigation = navigationEntries[0];
                
                // Time to First Byte (TTFB)
                const ttfb = navigation.responseStart - navigation.requestStart;
                this.recordMetric({
                    name: 'ttfb',
                    value: ttfb,
                    timestamp: Date.now(),
                    url: window.location.href,
                    type: 'navigation'
                });
                
                // DOM Content Loaded
                const domContentLoaded = navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart;
                this.recordMetric({
                    name: 'dom_content_loaded',
                    value: domContentLoaded,
                    timestamp: Date.now(),
                    url: window.location.href,
                    type: 'navigation'
                });
                
                // Page Load Time
                const pageLoadTime = navigation.loadEventEnd - navigation.navigationStart;
                this.recordMetric({
                    name: 'page_load_time',
                    value: pageLoadTime,
                    timestamp: Date.now(),
                    url: window.location.href,
                    type: 'navigation'
                });
            }
        }
    }

    /**
     * Setup Resource Timing monitoring
     */
    setupResourceTimingMonitoring() {
        if ('PerformanceObserver' in window) {
            const resourceObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                entries.forEach(entry => {
                    // Track slow resources
                    if (entry.duration > 1000) { // Resources taking more than 1 second
                        this.recordMetric({
                            name: 'slow_resource',
                            value: entry.duration,
                            resource: entry.name,
                            resourceType: entry.initiatorType,
                            timestamp: Date.now(),
                            url: window.location.href,
                            type: 'resource'
                        });
                    }
                    
                    // Track failed resources
                    if (entry.transferSize === 0 && entry.encodedBodySize > 0) {
                        this.recordMetric({
                            name: 'failed_resource',
                            resource: entry.name,
                            resourceType: entry.initiatorType,
                            timestamp: Date.now(),
                            url: window.location.href,
                            type: 'resource-error'
                        });
                    }
                });
            });
            
            resourceObserver.observe({ entryTypes: ['resource'] });
        }
    }

    /**
     * Setup Memory monitoring
     */
    setupMemoryMonitoring() {
        if ('memory' in performance) {
            setInterval(() => {
                const memInfo = performance.memory;
                const memoryUsagePercent = (memInfo.usedJSHeapSize / memInfo.jsHeapSizeLimit) * 100;
                
                this.recordMetric({
                    name: 'memory_usage',
                    value: memoryUsagePercent,
                    usedHeapSize: memInfo.usedJSHeapSize,
                    totalHeapSize: memInfo.totalJSHeapSize,
                    heapSizeLimit: memInfo.jsHeapSizeLimit,
                    timestamp: Date.now(),
                    url: window.location.href,
                    type: 'memory'
                });
                
                if (memoryUsagePercent > this.config.thresholds.memoryUsage) {
                    this.triggerAlert('Memory usage threshold exceeded', memoryUsagePercent);
                }
            }, 10000); // Check every 10 seconds
        }
    }

    /**
     * Setup User Timing monitoring
     */
    setupUserTimingMonitoring() {
        if ('PerformanceObserver' in window) {
            const userTimingObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                entries.forEach(entry => {
                    this.recordMetric({
                        name: `user_timing_${entry.name}`,
                        value: entry.duration || entry.startTime,
                        entryType: entry.entryType,
                        timestamp: Date.now(),
                        url: window.location.href,
                        type: 'user-timing'
                    });
                });
            });
            
            userTimingObserver.observe({ entryTypes: ['measure', 'mark'] });
        }
    }

    /**
     * Setup Custom Metrics for MesChain-Sync specific features
     */
    setupCustomMetrics() {
        // Track component render times
        this.trackComponentRenderTime = (componentName, startTime) => {
            const renderTime = performance.now() - startTime;
            this.recordMetric({
                name: 'component_render_time',
                component: componentName,
                value: renderTime,
                timestamp: Date.now(),
                url: window.location.href,
                type: 'custom'
            });
        };

        // Track API response times
        this.trackApiResponse = (endpoint, responseTime, status) => {
            this.recordMetric({
                name: 'api_response_time',
                endpoint: endpoint,
                value: responseTime,
                status: status,
                timestamp: Date.now(),
                url: window.location.href,
                type: 'api'
            });
        };

        // Track user interactions
        this.trackUserInteraction = (action, element, duration) => {
            this.recordMetric({
                name: 'user_interaction',
                action: action,
                element: element,
                value: duration,
                timestamp: Date.now(),
                url: window.location.href,
                type: 'interaction'
            });
        };
    }

    /**
     * Record a metric
     */
    recordMetric(metric) {
        // Apply sampling
        if (Math.random() > this.config.sampleRate) {
            return;
        }

        // Add session and user context
        metric.sessionId = this.getSessionId();
        metric.userId = this.getUserId();
        metric.deviceType = this.getDeviceType();
        metric.connectionType = this.getConnectionType();

        this.metricsQueue.push(metric);

        // Flush if batch size reached
        if (this.metricsQueue.length >= this.config.batchSize) {
            this.flushMetrics();
        }
    }

    /**
     * Flush metrics to server
     */
    async flushMetrics() {
        if (this.metricsQueue.length === 0) return;

        const metricsToSend = [...this.metricsQueue];
        this.metricsQueue = [];

        try {
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    metrics: metricsToSend,
                    timestamp: Date.now(),
                    source: 'selinay-frontend'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            console.log(`📊 Sent ${metricsToSend.length} performance metrics`);
        } catch (error) {
            console.error('❌ Failed to send performance metrics:', error);
            // Re-add metrics to queue for retry
            this.metricsQueue.unshift(...metricsToSend);
        }
    }

    /**
     * Start periodic collection and flushing
     */
    startPeriodicCollection() {
        setInterval(() => {
            this.collectPeriodicMetrics();
            this.flushMetrics();
        }, this.config.flushInterval);
    }

    /**
     * Collect periodic metrics
     */
    collectPeriodicMetrics() {
        // Collect FPS
        this.measureFPS();
        
        // Collect viewport information
        this.recordMetric({
            name: 'viewport_info',
            width: window.innerWidth,
            height: window.innerHeight,
            devicePixelRatio: window.devicePixelRatio,
            timestamp: Date.now(),
            url: window.location.href,
            type: 'viewport'
        });
    }

    /**
     * Measure FPS
     */
    measureFPS() {
        let fps = 0;
        let lastTime = performance.now();
        let frameCount = 0;

        const measureFrame = (currentTime) => {
            frameCount++;
            if (currentTime - lastTime >= 1000) {
                fps = Math.round((frameCount * 1000) / (currentTime - lastTime));
                
                this.recordMetric({
                    name: 'fps',
                    value: fps,
                    timestamp: Date.now(),
                    url: window.location.href,
                    type: 'rendering'
                });

                frameCount = 0;
                lastTime = currentTime;
            }
            requestAnimationFrame(measureFrame);
        };

        requestAnimationFrame(measureFrame);
    }

    /**
     * Trigger performance alert
     */
    triggerAlert(message, value) {
        if (!this.config.enableRealTimeAlerts) return;

        console.warn(`🚨 Performance Alert: ${message} - Value: ${value}`);
        
        // Send alert to monitoring system
        this.recordMetric({
            name: 'performance_alert',
            message: message,
            value: value,
            timestamp: Date.now(),
            url: window.location.href,
            type: 'alert'
        });
    }

    /**
     * Helper methods
     */
    getSessionId() {
        return sessionStorage.getItem('sessionId') || 'unknown';
    }

    getUserId() {
        return localStorage.getItem('userId') || 'anonymous';
    }

    getDeviceType() {
        return /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop';
    }

    getConnectionType() {
        return navigator.connection ? navigator.connection.effectiveType : 'unknown';
    }

    /**
     * Get current performance snapshot
     */
    getPerformanceSnapshot() {
        return {
            timestamp: Date.now(),
            url: window.location.href,
            metrics: {
                navigation: performance.getEntriesByType('navigation')[0],
                paint: performance.getEntriesByType('paint'),
                resources: performance.getEntriesByType('resource').length,
                memory: performance.memory ? {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit
                } : null
            }
        };
    }

    /**
     * Export metrics for analysis
     */
    exportMetrics() {
        return {
            queue: this.metricsQueue,
            config: this.config,
            snapshot: this.getPerformanceSnapshot()
        };
    }

    /**
     * Cleanup and destroy
     */
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        if (this.memoryObserver) {
            this.memoryObserver.disconnect();
        }
        this.flushMetrics();
        console.log('🧹 Performance Metrics Collector destroyed');
    }
}

// Initialize performance metrics collector
const selinayPerformanceMetrics = new PerformanceMetricsCollector({
    apiEndpoint: '/api/selinay/performance-metrics',
    sampleRate: 0.2, // 20% sampling for development
    enableRealTimeAlerts: true,
    thresholds: {
        lcp: 2000,    // Stricter threshold for enterprise
        fid: 80,      // Stricter threshold for enterprise
        cls: 0.08,    // Stricter threshold for enterprise
        fcp: 1500,    // Stricter threshold for enterprise
        ttfb: 500,    // Stricter threshold for enterprise
        memoryUsage: 40 // Stricter threshold for enterprise
    }
});

// Export for global access
window.SelinayPerformanceMetrics = selinayPerformanceMetrics;

// Expose helper functions for components
window.trackComponentRender = selinayPerformanceMetrics.trackComponentRenderTime.bind(selinayPerformanceMetrics);
window.trackApiResponse = selinayPerformanceMetrics.trackApiResponse.bind(selinayPerformanceMetrics);
window.trackUserInteraction = selinayPerformanceMetrics.trackUserInteraction.bind(selinayPerformanceMetrics);

console.log('🎯 Selinay Performance Metrics System loaded - Task 6 Implementation');
