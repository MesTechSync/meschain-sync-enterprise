/**
 * MesChain-Sync Enterprise - Error Tracking System
 * Selinay Team - Frontend UI/UX Specialist Task 6 Implementation
 * Advanced Frontend Error Monitoring & Logging
 * Date: June 6, 2025
 */

class SelinayErrorTracker {
    constructor(config = {}) {
        this.config = {
            apiEndpoint: config.apiEndpoint || '/api/error-tracking',
            maxStackFrames: config.maxStackFrames || 10,
            maxBreadcrumbs: config.maxBreadcrumbs || 50,
            enableConsoleCapture: config.enableConsoleCapture !== false,
            enableNetworkCapture: config.enableNetworkCapture !== false,
            enablePerformanceCapture: config.enablePerformanceCapture !== false,
            enableUserInteractionCapture: config.enableUserInteractionCapture !== false,
            sampling: config.sampling || 1.0, // 100% by default
            environment: config.environment || 'production',
            release: config.release || '1.0.0',
            userId: config.userId || null,
            sessionId: config.sessionId || this.generateSessionId(),
            ...config
        };

        this.breadcrumbs = [];
        this.userContext = {};
        this.deviceContext = {};
        this.errorBuffer = [];
        this.isInitialized = false;

        this.init();
    }

    /**
     * Initialize error tracking system
     */
    init() {
        if (this.isInitialized) return;

        try {
            this.setupGlobalErrorHandlers();
            this.setupUnhandledRejectionHandler();
            this.setupConsoleCapture();
            this.setupNetworkCapture();
            this.setupUserInteractionCapture();
            this.setupPerformanceCapture();
            this.captureDeviceContext();
            this.setupBeforeUnloadHandler();

            this.isInitialized = true;
            this.addBreadcrumb('Selinay Error Tracker initialized', 'system');
            console.log('✅ Selinay Error Tracker initialized successfully');
        } catch (error) {
            console.error('❌ Failed to initialize error tracker:', error);
        }
    }

    /**
     * Setup global error handlers
     */
    setupGlobalErrorHandlers() {
        // JavaScript errors
        window.addEventListener('error', (event) => {
            this.captureError({
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error,
                stack: event.error ? event.error.stack : null,
                timestamp: Date.now(),
                url: window.location.href,
                userAgent: navigator.userAgent
            });
        });

        // Resource loading errors
        window.addEventListener('error', (event) => {
            if (event.target !== window) {
                this.captureError({
                    type: 'resource',
                    message: `Failed to load resource: ${event.target.src || event.target.href}`,
                    element: event.target.tagName,
                    source: event.target.src || event.target.href,
                    timestamp: Date.now(),
                    url: window.location.href
                });
            }
        }, true);
    }

    /**
     * Setup unhandled promise rejection handler
     */
    setupUnhandledRejectionHandler() {
        window.addEventListener('unhandledrejection', (event) => {
            this.captureError({
                type: 'promise_rejection',
                message: event.reason ? event.reason.message || event.reason : 'Unhandled Promise Rejection',
                stack: event.reason ? event.reason.stack : null,
                reason: event.reason,
                timestamp: Date.now(),
                url: window.location.href
            });
        });
    }

    /**
     * Setup console capture
     */
    setupConsoleCapture() {
        if (!this.config.enableConsoleCapture) return;

        const originalConsoleError = console.error;
        const originalConsoleWarn = console.warn;

        console.error = (...args) => {
            this.captureError({
                type: 'console_error',
                message: args.join(' '),
                args: args,
                timestamp: Date.now(),
                url: window.location.href
            });
            originalConsoleError.apply(console, args);
        };

        console.warn = (...args) => {
            this.addBreadcrumb(`Console Warning: ${args.join(' ')}`, 'console');
            originalConsoleWarn.apply(console, args);
        };
    }

    /**
     * Setup network error capture
     */
    setupNetworkCapture() {
        if (!this.config.enableNetworkCapture) return;

        // Intercept fetch requests
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            const startTime = performance.now();
            const url = args[0];
            const options = args[1] || {};

            try {
                const response = await originalFetch.apply(this, args);
                const duration = performance.now() - startTime;

                this.addBreadcrumb(
                    `HTTP ${options.method || 'GET'} ${url} - ${response.status}`,
                    'http',
                    { duration, status: response.status }
                );

                // Capture HTTP errors
                if (!response.ok) {
                    this.captureError({
                        type: 'http_error',
                        message: `HTTP ${response.status}: ${response.statusText}`,
                        url: url,
                        method: options.method || 'GET',
                        status: response.status,
                        statusText: response.statusText,
                        duration: duration,
                        timestamp: Date.now(),
                        requestUrl: window.location.href
                    });
                }

                return response;
            } catch (error) {
                const duration = performance.now() - startTime;
                
                this.captureError({
                    type: 'network_error',
                    message: `Network error: ${error.message}`,
                    url: url,
                    method: options.method || 'GET',
                    error: error,
                    duration: duration,
                    timestamp: Date.now(),
                    requestUrl: window.location.href
                });

                throw error;
            }
        };

        // Intercept XMLHttpRequest
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            this._selinayMethod = method;
            this._selinayUrl = url;
            this._selinayStartTime = performance.now();
            return originalXHROpen.call(this, method, url, ...args);
        };

        XMLHttpRequest.prototype.send = function(...args) {
            this.addEventListener('error', () => {
                const duration = performance.now() - this._selinayStartTime;
                selinayErrorTracker.captureError({
                    type: 'xhr_error',
                    message: `XHR error: ${this._selinayMethod} ${this._selinayUrl}`,
                    url: this._selinayUrl,
                    method: this._selinayMethod,
                    duration: duration,
                    timestamp: Date.now(),
                    requestUrl: window.location.href
                });
            });

            this.addEventListener('timeout', () => {
                const duration = performance.now() - this._selinayStartTime;
                selinayErrorTracker.captureError({
                    type: 'xhr_timeout',
                    message: `XHR timeout: ${this._selinayMethod} ${this._selinayUrl}`,
                    url: this._selinayUrl,
                    method: this._selinayMethod,
                    duration: duration,
                    timestamp: Date.now(),
                    requestUrl: window.location.href
                });
            });

            return originalXHRSend.call(this, ...args);
        };
    }

    /**
     * Setup user interaction capture
     */
    setupUserInteractionCapture() {
        if (!this.config.enableUserInteractionCapture) return;

        ['click', 'submit', 'keydown', 'focus', 'blur'].forEach(eventType => {
            document.addEventListener(eventType, (event) => {
                this.addBreadcrumb(
                    `User ${eventType} on ${event.target.tagName}${event.target.id ? '#' + event.target.id : ''}`,
                    'user',
                    { 
                        element: event.target.tagName,
                        id: event.target.id,
                        className: event.target.className
                    }
                );
            }, true);
        });
    }

    /**
     * Setup performance capture
     */
    setupPerformanceCapture() {
        if (!this.config.enablePerformanceCapture) return;

        // Capture long tasks
        if ('PerformanceObserver' in window) {
            try {
                const longTaskObserver = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.duration > 50) { // Tasks longer than 50ms
                            this.captureError({
                                type: 'performance_long_task',
                                message: `Long task detected: ${entry.duration}ms`,
                                duration: entry.duration,
                                startTime: entry.startTime,
                                timestamp: Date.now(),
                                url: window.location.href
                            });
                        }
                    }
                });
                
                longTaskObserver.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                // Long task API not supported
            }
        }

        // Capture memory warnings
        if ('memory' in performance) {
            setInterval(() => {
                const memInfo = performance.memory;
                const memoryUsagePercent = (memInfo.usedJSHeapSize / memInfo.jsHeapSizeLimit) * 100;
                
                if (memoryUsagePercent > 90) {
                    this.captureError({
                        type: 'memory_warning',
                        message: `High memory usage: ${memoryUsagePercent.toFixed(1)}%`,
                        memoryUsage: memoryUsagePercent,
                        usedHeapSize: memInfo.usedJSHeapSize,
                        totalHeapSize: memInfo.totalJSHeapSize,
                        heapSizeLimit: memInfo.jsHeapSizeLimit,
                        timestamp: Date.now(),
                        url: window.location.href
                    });
                }
            }, 30000); // Check every 30 seconds
        }
    }

    /**
     * Capture device and browser context
     */
    captureDeviceContext() {
        this.deviceContext = {
            userAgent: navigator.userAgent,
            language: navigator.language,
            languages: navigator.languages,
            platform: navigator.platform,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine,
            screen: {
                width: screen.width,
                height: screen.height,
                colorDepth: screen.colorDepth,
                pixelDepth: screen.pixelDepth
            },
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            },
            devicePixelRatio: window.devicePixelRatio,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            connection: navigator.connection ? {
                effectiveType: navigator.connection.effectiveType,
                downlink: navigator.connection.downlink,
                rtt: navigator.connection.rtt
            } : null
        };
    }

    /**
     * Setup beforeunload handler to send remaining errors
     */
    setupBeforeUnloadHandler() {
        window.addEventListener('beforeunload', () => {
            this.flushErrors(true);
        });
    }

    /**
     * Capture an error
     */
    captureError(errorData) {
        // Apply sampling
        if (Math.random() > this.config.sampling) {
            return;
        }

        const error = {
            id: this.generateErrorId(),
            ...errorData,
            breadcrumbs: [...this.breadcrumbs],
            userContext: this.userContext,
            deviceContext: this.deviceContext,
            sessionId: this.config.sessionId,
            userId: this.config.userId,
            environment: this.config.environment,
            release: this.config.release,
            url: window.location.href,
            referrer: document.referrer
        };

        // Clean up stack trace
        if (error.stack) {
            error.stack = this.cleanStackTrace(error.stack);
        }

        // Add to buffer
        this.errorBuffer.push(error);

        // Add breadcrumb for this error
        this.addBreadcrumb(
            `Error: ${error.message}`,
            'error',
            { type: error.type }
        );

        // Send immediately for critical errors
        if (this.isCriticalError(error)) {
            this.sendError(error);
        } else {
            // Batch send after a delay
            setTimeout(() => this.flushErrors(), 1000);
        }

        console.error('🐛 Selinay Error Captured:', error.message);
    }

    /**
     * Check if error is critical
     */
    isCriticalError(error) {
        const criticalTypes = ['javascript', 'promise_rejection', 'network_error'];
        return criticalTypes.includes(error.type);
    }

    /**
     * Add breadcrumb
     */
    addBreadcrumb(message, category = 'manual', data = {}) {
        const breadcrumb = {
            message,
            category,
            data,
            timestamp: Date.now(),
            level: 'info'
        };

        this.breadcrumbs.push(breadcrumb);

        // Keep only recent breadcrumbs
        if (this.breadcrumbs.length > this.config.maxBreadcrumbs) {
            this.breadcrumbs.shift();
        }
    }

    /**
     * Set user context
     */
    setUserContext(context) {
        this.userContext = { ...this.userContext, ...context };
    }

    /**
     * Clean stack trace
     */
    cleanStackTrace(stack) {
        if (!stack) return null;

        const lines = stack.split('\n').slice(0, this.config.maxStackFrames);
        return lines.join('\n');
    }

    /**
     * Generate unique error ID
     */
    generateErrorId() {
        return 'err_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Generate session ID
     */
    generateSessionId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Send error to server
     */
    async sendError(error) {
        try {
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    error,
                    timestamp: Date.now(),
                    source: 'selinay-frontend-error-tracker'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            console.log(`📤 Error sent to server: ${error.id}`);
        } catch (sendError) {
            console.error('❌ Failed to send error to server:', sendError);
            // Store in localStorage as fallback
            this.storeErrorLocally(error);
        }
    }

    /**
     * Flush all buffered errors
     */
    async flushErrors(force = false) {
        if (this.errorBuffer.length === 0) return;

        const errorsToSend = [...this.errorBuffer];
        this.errorBuffer = [];

        try {
            const response = await fetch(this.config.apiEndpoint + '/batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    errors: errorsToSend,
                    timestamp: Date.now(),
                    source: 'selinay-frontend-error-tracker'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            console.log(`📤 Sent ${errorsToSend.length} errors to server`);
        } catch (error) {
            console.error('❌ Failed to send error batch:', error);
            // Re-add to buffer if not forcing
            if (!force) {
                this.errorBuffer.unshift(...errorsToSend);
            }
            // Store in localStorage as fallback
            errorsToSend.forEach(err => this.storeErrorLocally(err));
        }
    }

    /**
     * Store error locally as fallback
     */
    storeErrorLocally(error) {
        try {
            const localErrors = JSON.parse(localStorage.getItem('selinay_errors') || '[]');
            localErrors.push(error);
            
            // Keep only recent 100 errors locally
            if (localErrors.length > 100) {
                localErrors.splice(0, localErrors.length - 100);
            }
            
            localStorage.setItem('selinay_errors', JSON.stringify(localErrors));
        } catch (storageError) {
            console.error('❌ Failed to store error locally:', storageError);
        }
    }

    /**
     * Get locally stored errors
     */
    getLocalErrors() {
        try {
            return JSON.parse(localStorage.getItem('selinay_errors') || '[]');
        } catch (error) {
            return [];
        }
    }

    /**
     * Clear locally stored errors
     */
    clearLocalErrors() {
        localStorage.removeItem('selinay_errors');
    }

    /**
     * Get error statistics
     */
    getErrorStats() {
        const localErrors = this.getLocalErrors();
        const stats = {
            totalErrors: localErrors.length,
            errorTypes: {},
            recentErrors: localErrors.slice(-10),
            bufferSize: this.errorBuffer.length
        };

        localErrors.forEach(error => {
            stats.errorTypes[error.type] = (stats.errorTypes[error.type] || 0) + 1;
        });

        return stats;
    }

    /**
     * Manual error reporting
     */
    reportError(message, extra = {}) {
        this.captureError({
            type: 'manual',
            message: message,
            extra: extra,
            timestamp: Date.now(),
            url: window.location.href
        });
    }

    /**
     * Test error tracking
     */
    testErrorTracking() {
        console.log('🧪 Testing Selinay Error Tracking...');
        
        // Test manual error
        this.reportError('Test manual error', { test: true });
        
        // Test JavaScript error (commented out to avoid actual error)
        // throw new Error('Test JavaScript error');
        
        // Test network error simulation
        fetch('/test-404-endpoint').catch(() => {
            console.log('✅ Network error test completed');
        });
        
        console.log('✅ Error tracking test completed');
    }

    /**
     * Destroy error tracker
     */
    destroy() {
        this.flushErrors(true);
        this.clearLocalErrors();
        console.log('🧹 Selinay Error Tracker destroyed');
    }
}

// Initialize error tracker
const selinayErrorTracker = new SelinayErrorTracker({
    apiEndpoint: '/api/selinay/error-tracking',
    environment: 'production',
    release: '1.0.0',
    sampling: 1.0, // 100% sampling for production monitoring
    enableConsoleCapture: true,
    enableNetworkCapture: true,
    enablePerformanceCapture: true,
    enableUserInteractionCapture: true
});

// Export for global access
window.SelinayErrorTracker = selinayErrorTracker;

// Expose helper functions
window.reportError = selinayErrorTracker.reportError.bind(selinayErrorTracker);
window.addBreadcrumb = selinayErrorTracker.addBreadcrumb.bind(selinayErrorTracker);
window.setUserContext = selinayErrorTracker.setUserContext.bind(selinayErrorTracker);

console.log('🛡️ Selinay Error Tracking System loaded - Task 6 Implementation');
