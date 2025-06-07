/**
 * Advanced Browser Analytics & Performance Monitor
 * MesChain-Sync Enhanced - Cross-Browser Testing Suite
 * Version: 2.1.0
 * Author: MesTech Solutions
 * Date: December 2024
 */

class AdvancedBrowserAnalytics {
    constructor() {
        this.analytics = {
            browserMetrics: {},
            performanceData: [],
            errorLogs: [],
            userInteractions: [],
            resourceLoadTimes: {},
            memoryUsage: [],
            networkInfo: null,
            deviceInfo: null,
            startTime: performance.now(),
            sessionId: this.generateSessionId()
        };
        
        this.thresholds = {
            loadTime: 3000, // 3 seconds
            memoryUsage: 100 * 1024 * 1024, // 100MB
            errorRate: 0.05, // 5% error rate
            interactionDelay: 100 // 100ms
        };
        
        this.initializeAnalytics();
    }
    
    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    initializeAnalytics() {
        console.log('🔍 Advanced Browser Analytics başlatılıyor...');
        
        // Collect browser information
        this.collectBrowserInfo();
        
        // Collect device information
        this.collectDeviceInfo();
        
        // Monitor performance
        this.monitorPerformance();
        
        // Track errors
        this.trackErrors();
        
        // Monitor user interactions
        this.monitorUserInteractions();
        
        // Track resource loading
        this.trackResourceLoading();
        
        // Monitor memory usage
        this.monitorMemoryUsage();
        
        // Network information
        this.collectNetworkInfo();
        
        console.log('✅ Advanced Browser Analytics aktif');
    }
    
    collectBrowserInfo() {
        const browserInfo = {
            userAgent: navigator.userAgent,
            language: navigator.language,
            languages: navigator.languages,
            platform: navigator.platform,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine,
            doNotTrack: navigator.doNotTrack,
            hardwareConcurrency: navigator.hardwareConcurrency,
            maxTouchPoints: navigator.maxTouchPoints || 0,
            vendor: navigator.vendor,
            vendorSub: navigator.vendorSub,
            product: navigator.product,
            productSub: navigator.productSub,
            appName: navigator.appName,
            appVersion: navigator.appVersion,
            appCodeName: navigator.appCodeName,
            geolocation: !!navigator.geolocation,
            webdriver: navigator.webdriver,
            timestamp: new Date().toISOString()
        };
        
        // Detect browser type and version
        const browserDetection = this.detectBrowser();
        browserInfo.browser = browserDetection;
        
        // Screen information
        browserInfo.screen = {
            width: screen.width,
            height: screen.height,
            availWidth: screen.availWidth,
            availHeight: screen.availHeight,
            colorDepth: screen.colorDepth,
            pixelDepth: screen.pixelDepth,
            orientation: screen.orientation ? screen.orientation.type : 'unknown'
        };
        
        // Window information
        browserInfo.window = {
            innerWidth: window.innerWidth,
            innerHeight: window.innerHeight,
            outerWidth: window.outerWidth,
            outerHeight: window.outerHeight,
            devicePixelRatio: window.devicePixelRatio,
            scrollX: window.scrollX,
            scrollY: window.scrollY
        };
        
        this.analytics.browserMetrics = browserInfo;
    }
    
    detectBrowser() {
        const ua = navigator.userAgent;
        let browser = { name: 'Unknown', version: 'Unknown', engine: 'Unknown' };
        
        // Chrome
        if (ua.includes('Chrome') && !ua.includes('Edg') && !ua.includes('OPR')) {
            const match = ua.match(/Chrome\/([0-9]+)/);
            browser = {
                name: 'Chrome',
                version: match ? match[1] : 'Unknown',
                engine: 'Blink'
            };
        }
        // Firefox
        else if (ua.includes('Firefox')) {
            const match = ua.match(/Firefox\/([0-9]+)/);
            browser = {
                name: 'Firefox',
                version: match ? match[1] : 'Unknown',
                engine: 'Gecko'
            };
        }
        // Safari
        else if (ua.includes('Safari') && !ua.includes('Chrome')) {
            const match = ua.match(/Version\/([0-9]+)/);
            browser = {
                name: 'Safari',
                version: match ? match[1] : 'Unknown',
                engine: 'WebKit'
            };
        }
        // Edge
        else if (ua.includes('Edg')) {
            const match = ua.match(/Edg\/([0-9]+)/);
            browser = {
                name: 'Edge',
                version: match ? match[1] : 'Unknown',
                engine: 'Blink'
            };
        }
        // Opera
        else if (ua.includes('OPR')) {
            const match = ua.match(/OPR\/([0-9]+)/);
            browser = {
                name: 'Opera',
                version: match ? match[1] : 'Unknown',
                engine: 'Blink'
            };
        }
        // Internet Explorer
        else if (ua.includes('Trident') || ua.includes('MSIE')) {
            const match = ua.match(/rv:([0-9]+)/) || ua.match(/MSIE ([0-9]+)/);
            browser = {
                name: 'Internet Explorer',
                version: match ? match[1] : 'Unknown',
                engine: 'Trident'
            };
        }
        
        return browser;
    }
    
    collectDeviceInfo() {
        const deviceInfo = {
            type: this.getDeviceType(),
            isMobile: /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
            isTablet: /iPad|Android(?!.*Mobile)/i.test(navigator.userAgent),
            isDesktop: !/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
            touchSupport: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
            timestamp: new Date().toISOString()
        };
        
        // Battery API (if available)
        if ('getBattery' in navigator) {
            navigator.getBattery().then(battery => {
                deviceInfo.battery = {
                    level: battery.level,
                    charging: battery.charging,
                    chargingTime: battery.chargingTime,
                    dischargingTime: battery.dischargingTime
                };
            }).catch(() => {
                deviceInfo.battery = null;
            });
        }
        
        this.analytics.deviceInfo = deviceInfo;
    }
    
    getDeviceType() {
        const ua = navigator.userAgent;
        if (/tablet|ipad|playbook|silk/i.test(ua)) {
            return 'tablet';
        }
        if (/mobile|iphone|ipod|android|blackberry|opera|mini|windows\sce|palm|smartphone|iemobile/i.test(ua)) {
            return 'mobile';
        }
        return 'desktop';
    }
    
    monitorPerformance() {
        // Navigation timing
        if (performance.navigation && performance.timing) {
            const timing = performance.timing;
            const navigation = performance.navigation;
            
            const performanceData = {
                type: 'navigation',
                redirectCount: navigation.redirectCount,
                navigationType: navigation.type,
                dns: timing.domainLookupEnd - timing.domainLookupStart,
                tcp: timing.connectEnd - timing.connectStart,
                ssl: timing.secureConnectionStart > 0 ? timing.connectEnd - timing.secureConnectionStart : 0,
                ttfb: timing.responseStart - timing.navigationStart,
                download: timing.responseEnd - timing.responseStart,
                domInteractive: timing.domInteractive - timing.navigationStart,
                domComplete: timing.domComplete - timing.navigationStart,
                loadComplete: timing.loadEventEnd - timing.navigationStart,
                timestamp: new Date().toISOString()
            };
            
            this.analytics.performanceData.push(performanceData);
        }
        
        // Resource timing
        if (performance.getEntriesByType) {
            const resources = performance.getEntriesByType('resource');
            resources.forEach(resource => {
                this.analytics.resourceLoadTimes[resource.name] = {
                    duration: resource.duration,
                    size: resource.transferSize || 0,
                    type: resource.initiatorType,
                    timestamp: new Date().toISOString()
                };
            });
        }
        
        // Long task observer
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    list.getEntries().forEach(entry => {
                        if (entry.duration > 50) { // Long task > 50ms
                            this.analytics.performanceData.push({
                                type: 'long-task',
                                duration: entry.duration,
                                startTime: entry.startTime,
                                timestamp: new Date().toISOString()
                            });
                        }
                    });
                });
                observer.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                console.warn('Long task observer desteklenmiyor:', e);
            }
        }
        
        // First Paint, First Contentful Paint
        if (performance.getEntriesByType) {
            const paintEntries = performance.getEntriesByType('paint');
            paintEntries.forEach(entry => {
                this.analytics.performanceData.push({
                    type: 'paint',
                    name: entry.name,
                    startTime: entry.startTime,
                    timestamp: new Date().toISOString()
                });
            });
        }
    }
    
    trackErrors() {
        // JavaScript errors
        window.addEventListener('error', (event) => {
            this.analytics.errorLogs.push({
                type: 'javascript',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error ? event.error.stack : null,
                timestamp: new Date().toISOString()
            });
        });
        
        // Promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.analytics.errorLogs.push({
                type: 'promise_rejection',
                reason: event.reason,
                promise: event.promise,
                timestamp: new Date().toISOString()
            });
        });
        
        // Resource loading errors
        window.addEventListener('error', (event) => {
            if (event.target !== window) {
                this.analytics.errorLogs.push({
                    type: 'resource',
                    element: event.target.tagName,
                    source: event.target.src || event.target.href,
                    timestamp: new Date().toISOString()
                });
            }
        }, true);
    }
    
    monitorUserInteractions() {
        const interactionTypes = ['click', 'keydown', 'scroll', 'resize', 'focus', 'blur'];
        
        interactionTypes.forEach(type => {
            document.addEventListener(type, (event) => {
                this.analytics.userInteractions.push({
                    type: type,
                    timestamp: new Date().toISOString(),
                    target: event.target.tagName,
                    x: event.clientX || 0,
                    y: event.clientY || 0
                });
                
                // Keep only last 100 interactions to prevent memory bloat
                if (this.analytics.userInteractions.length > 100) {
                    this.analytics.userInteractions = this.analytics.userInteractions.slice(-100);
                }
            });
        });
    }
    
    trackResourceLoading() {
        // Observe resource loading
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    list.getEntries().forEach(entry => {
                        if (entry.entryType === 'resource') {
                            this.analytics.resourceLoadTimes[entry.name] = {
                                duration: entry.duration,
                                size: entry.transferSize || 0,
                                type: entry.initiatorType,
                                startTime: entry.startTime,
                                timestamp: new Date().toISOString()
                            };
                        }
                    });
                });
                observer.observe({ entryTypes: ['resource'] });
            } catch (e) {
                console.warn('Resource observer desteklenmiyor:', e);
            }
        }
    }
    
    monitorMemoryUsage() {
        if (performance.memory) {
            const checkMemory = () => {
                const memory = {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit,
                    timestamp: new Date().toISOString()
                };
                
                this.analytics.memoryUsage.push(memory);
                
                // Keep only last 50 memory readings
                if (this.analytics.memoryUsage.length > 50) {
                    this.analytics.memoryUsage = this.analytics.memoryUsage.slice(-50);
                }
                
                // Check for memory leaks
                if (memory.used > this.thresholds.memoryUsage) {
                    console.warn('⚠️ Yüksek bellek kullanımı tespit edildi:', memory.used / (1024 * 1024), 'MB');
                }
            };
            
            // Check memory every 5 seconds
            setInterval(checkMemory, 5000);
            checkMemory(); // Initial check
        }
    }
    
    collectNetworkInfo() {
        // Connection API
        if ('connection' in navigator || 'mozConnection' in navigator || 'webkitConnection' in navigator) {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            
            this.analytics.networkInfo = {
                effectiveType: connection.effectiveType,
                downlink: connection.downlink,
                rtt: connection.rtt,
                saveData: connection.saveData,
                type: connection.type,
                timestamp: new Date().toISOString()
            };
            
            // Listen for network changes
            connection.addEventListener('change', () => {
                this.analytics.networkInfo = {
                    effectiveType: connection.effectiveType,
                    downlink: connection.downlink,
                    rtt: connection.rtt,
                    saveData: connection.saveData,
                    type: connection.type,
                    timestamp: new Date().toISOString()
                };
            });
        }
        
        // Online/offline status
        window.addEventListener('online', () => {
            this.analytics.networkInfo = {
                ...this.analytics.networkInfo,
                status: 'online',
                timestamp: new Date().toISOString()
            };
        });
        
        window.addEventListener('offline', () => {
            this.analytics.networkInfo = {
                ...this.analytics.networkInfo,
                status: 'offline',
                timestamp: new Date().toISOString()
            };
        });
    }
    
    generateReport() {
        const currentTime = performance.now();
        const sessionDuration = currentTime - this.analytics.startTime;
        
        const report = {
            sessionId: this.analytics.sessionId,
            sessionDuration: sessionDuration,
            timestamp: new Date().toISOString(),
            
            // Browser & Device Info
            browser: this.analytics.browserMetrics.browser,
            device: this.analytics.deviceInfo,
            screen: this.analytics.browserMetrics.screen,
            
            // Performance Metrics
            performance: {
                averageLoadTime: this.calculateAverageLoadTime(),
                memoryPeak: this.getMemoryPeak(),
                errorRate: this.calculateErrorRate(),
                interactionCount: this.analytics.userInteractions.length,
                resourceCount: Object.keys(this.analytics.resourceLoadTimes).length
            },
            
            // Detailed Data
            performanceData: this.analytics.performanceData,
            errors: this.analytics.errorLogs,
            memoryUsage: this.analytics.memoryUsage,
            networkInfo: this.analytics.networkInfo,
            
            // Health Score
            healthScore: this.calculateHealthScore(),
            
            // Recommendations
            recommendations: this.generateRecommendations()
        };
        
        return report;
    }
    
    calculateAverageLoadTime() {
        const loadTimes = this.analytics.performanceData
            .filter(p => p.type === 'navigation' && p.loadComplete)
            .map(p => p.loadComplete);
        
        return loadTimes.length > 0 ? loadTimes.reduce((a, b) => a + b, 0) / loadTimes.length : 0;
    }
    
    getMemoryPeak() {
        return this.analytics.memoryUsage.length > 0 
            ? Math.max(...this.analytics.memoryUsage.map(m => m.used))
            : 0;
    }
    
    calculateErrorRate() {
        const totalInteractions = this.analytics.userInteractions.length;
        const totalErrors = this.analytics.errorLogs.length;
        
        return totalInteractions > 0 ? totalErrors / totalInteractions : 0;
    }
    
    calculateHealthScore() {
        let score = 100;
        
        // Performance penalties
        const avgLoadTime = this.calculateAverageLoadTime();
        if (avgLoadTime > this.thresholds.loadTime) {
            score -= Math.min(30, (avgLoadTime - this.thresholds.loadTime) / 1000 * 10);
        }
        
        // Memory penalties
        const memoryPeak = this.getMemoryPeak();
        if (memoryPeak > this.thresholds.memoryUsage) {
            score -= Math.min(20, (memoryPeak - this.thresholds.memoryUsage) / (1024 * 1024) * 2);
        }
        
        // Error penalties
        const errorRate = this.calculateErrorRate();
        if (errorRate > this.thresholds.errorRate) {
            score -= Math.min(25, errorRate * 100);
        }
        
        // Browser compatibility bonus/penalty
        const browser = this.analytics.browserMetrics.browser;
        if (['Chrome', 'Firefox', 'Safari', 'Edge'].includes(browser.name)) {
            score += 5;
        }
        
        return Math.max(0, Math.min(100, Math.round(score)));
    }
    
    generateRecommendations() {
        const recommendations = [];
        
        // Performance recommendations
        const avgLoadTime = this.calculateAverageLoadTime();
        if (avgLoadTime > this.thresholds.loadTime) {
            recommendations.push({
                type: 'performance',
                priority: 'high',
                message: `Sayfa yükleme süresi ${Math.round(avgLoadTime/1000)}s. 3s altına düşürülmeli.`,
                action: 'Optimize resources, enable caching, minimize JavaScript'
            });
        }
        
        // Memory recommendations
        const memoryPeak = this.getMemoryPeak();
        if (memoryPeak > this.thresholds.memoryUsage) {
            recommendations.push({
                type: 'memory',
                priority: 'medium',
                message: `Yüksek bellek kullanımı: ${Math.round(memoryPeak/(1024*1024))}MB`,
                action: 'Check for memory leaks, optimize data structures'
            });
        }
        
        // Error recommendations
        const errorRate = this.calculateErrorRate();
        if (errorRate > this.thresholds.errorRate) {
            recommendations.push({
                type: 'stability',
                priority: 'high',
                message: `Yüksek hata oranı: %${Math.round(errorRate*100)}`,
                action: 'Fix JavaScript errors, improve error handling'
            });
        }
        
        // Browser compatibility
        const browser = this.analytics.browserMetrics.browser;
        if (!['Chrome', 'Firefox', 'Safari', 'Edge'].includes(browser.name)) {
            recommendations.push({
                type: 'compatibility',
                priority: 'low',
                message: `Eski tarayıcı tespit edildi: ${browser.name}`,
                action: 'Test with modern browsers, add polyfills if needed'
            });
        }
        
        // Network recommendations
        if (this.analytics.networkInfo && this.analytics.networkInfo.effectiveType === 'slow-2g') {
            recommendations.push({
                type: 'network',
                priority: 'medium',
                message: 'Yavaş internet bağlantısı tespit edildi',
                action: 'Optimize images, enable compression, reduce bundle size'
            });
        }
        
        return recommendations;
    }
    
    // Public API methods
    getAnalytics() {
        return this.analytics;
    }
    
    getHealthScore() {
        return this.calculateHealthScore();
    }
    
    exportReport() {
        const report = this.generateReport();
        
        // Create downloadable JSON file
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `browser-analytics-${this.analytics.sessionId}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        return report;
    }
    
    sendReportToServer(endpoint = '/api/analytics') {
        const report = this.generateReport();
        
        return fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(report)
        }).then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }).catch(error => {
            console.error('Analytics raporu gönderilemedi:', error);
            throw error;
        });
    }
    
    startRealTimeMonitoring(callback, interval = 10000) {
        return setInterval(() => {
            const report = this.generateReport();
            callback(report);
        }, interval);
    }
    
    stopRealTimeMonitoring(intervalId) {
        clearInterval(intervalId);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Create global instance
    window.advancedBrowserAnalytics = new AdvancedBrowserAnalytics();
    
    // Add console commands for debugging
    window.getBrowserAnalytics = () => window.advancedBrowserAnalytics.getAnalytics();
    window.getHealthScore = () => window.advancedBrowserAnalytics.getHealthScore();
    window.exportAnalyticsReport = () => window.advancedBrowserAnalytics.exportReport();
    window.generateAnalyticsReport = () => window.advancedBrowserAnalytics.generateReport();
    
    console.log('🔍 Advanced Browser Analytics hazır!');
    console.log('Kullanılabilir komutlar:');
    console.log('- getBrowserAnalytics(): Tüm analitik verilerini görüntüle');
    console.log('- getHealthScore(): Sağlık skorunu görüntüle');
    console.log('- exportAnalyticsReport(): Raporu JSON olarak indir');
    console.log('- generateAnalyticsReport(): Raporu konsola yazdır');
    
    // Auto-export report on page unload
    window.addEventListener('beforeunload', () => {
        const report = window.advancedBrowserAnalytics.generateReport();
        localStorage.setItem('lastBrowserAnalyticsReport', JSON.stringify(report));
    });
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdvancedBrowserAnalytics;
}
