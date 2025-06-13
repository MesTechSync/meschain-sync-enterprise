/* 📊 SELINAY PERFORMANCE MONITOR - MesChain-Sync Enterprise */
/* Real-time Performance Tracking & Lighthouse Integration */
/* Created: June 5, 2025 06:00 UTC */

class SelinayPerformanceMonitor {
    constructor() {
        this.metrics = {};
        this.observers = [];
        this.isMonitoring = false;
        this.startTime = performance.now();
        this.reportInterval = null;
        
        console.log('📊 Selinay Performance Monitor Initialized');
        this.init();
    }

    init() {
        this.setupPerformanceObservers();
        this.startMonitoring();
        this.createPerformanceWidget();
        this.scheduleReports();
        
        console.log('⚡ Performance monitoring started');
    }

    setupPerformanceObservers() {
        // Navigation Timing Observer
        if ('PerformanceObserver' in window) {
            try {
                const navObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => this.processNavigationEntry(entry));
                });
                navObserver.observe({ entryTypes: ['navigation'] });
                this.observers.push(navObserver);
            } catch (e) {
                console.warn('Navigation timing observer not supported');
            }

            // Resource Timing Observer
            try {
                const resourceObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => this.processResourceEntry(entry));
                });
                resourceObserver.observe({ entryTypes: ['resource'] });
                this.observers.push(resourceObserver);
            } catch (e) {
                console.warn('Resource timing observer not supported');
            }

            // Paint Timing Observer
            try {
                const paintObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => this.processPaintEntry(entry));
                });
                paintObserver.observe({ entryTypes: ['paint'] });
                this.observers.push(paintObserver);
            } catch (e) {
                console.warn('Paint timing observer not supported');
            }

            // Layout Shift Observer
            try {
                const clsObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => this.processLayoutShiftEntry(entry));
                });
                clsObserver.observe({ entryTypes: ['layout-shift'] });
                this.observers.push(clsObserver);
            } catch (e) {
                console.warn('Layout shift observer not supported');
            }
        }
    }

    processNavigationEntry(entry) {
        this.metrics.navigation = {
            domContentLoaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
            loadComplete: entry.loadEventEnd - entry.loadEventStart,
            domInteractive: entry.domInteractive - entry.fetchStart,
            ttfb: entry.responseStart - entry.requestStart,
            dns: entry.domainLookupEnd - entry.domainLookupStart,
            tcp: entry.connectEnd - entry.connectStart,
            timestamp: Date.now()
        };

        this.calculateCoreWebVitals();
        this.updatePerformanceWidget();
    }

    processResourceEntry(entry) {
        if (!this.metrics.resources) this.metrics.resources = [];
        
        const resourceMetric = {
            name: entry.name,
            type: this.getResourceType(entry.name),
            duration: entry.duration,
            size: entry.transferSize || 0,
            cached: entry.transferSize === 0 && entry.decodedBodySize > 0,
            timestamp: Date.now()
        };

        this.metrics.resources.push(resourceMetric);
        
        // Keep only last 100 resources to prevent memory issues
        if (this.metrics.resources.length > 100) {
            this.metrics.resources = this.metrics.resources.slice(-100);
        }
    }

    processPaintEntry(entry) {
        if (!this.metrics.paint) this.metrics.paint = {};
        
        this.metrics.paint[entry.name] = {
            time: entry.startTime,
            timestamp: Date.now()
        };

        // Calculate FCP (First Contentful Paint)
        if (entry.name === 'first-contentful-paint') {
            this.metrics.fcp = entry.startTime;
        }

        this.updatePerformanceWidget();
    }

    processLayoutShiftEntry(entry) {
        if (!this.metrics.layoutShifts) this.metrics.layoutShifts = [];
        
        this.metrics.layoutShifts.push({
            value: entry.value,
            hadRecentInput: entry.hadRecentInput,
            timestamp: Date.now()
        });

        // Calculate CLS (Cumulative Layout Shift)
        this.calculateCLS();
    }

    calculateCoreWebVitals() {
        const vitals = {};

        // LCP (Largest Contentful Paint) - approximation
        if (this.metrics.navigation) {
            vitals.lcp = this.metrics.navigation.loadComplete;
        }

        // FID (First Input Delay) - will be measured on first interaction
        vitals.fid = this.metrics.fid || 0;

        // CLS (Cumulative Layout Shift)
        vitals.cls = this.calculateCLS();

        // FCP (First Contentful Paint)
        vitals.fcp = this.metrics.fcp || 0;

        // TTFB (Time to First Byte)
        vitals.ttfb = this.metrics.navigation ? this.metrics.navigation.ttfb : 0;

        this.metrics.coreWebVitals = vitals;
        this.evaluatePerformanceScore();
    }

    calculateCLS() {
        if (!this.metrics.layoutShifts || this.metrics.layoutShifts.length === 0) {
            return 0;
        }

        // Sum layout shift values (simplified calculation)
        const totalShift = this.metrics.layoutShifts
            .filter(shift => !shift.hadRecentInput)
            .reduce((sum, shift) => sum + shift.value, 0);

        return Math.min(totalShift, 1); // Cap at 1
    }

    evaluatePerformanceScore() {
        const vitals = this.metrics.coreWebVitals;
        if (!vitals) return 0;

        let score = 100;

        // LCP scoring (< 2.5s = good, 2.5s-4s = needs improvement, > 4s = poor)
        if (vitals.lcp > 4000) score -= 30;
        else if (vitals.lcp > 2500) score -= 15;

        // FCP scoring (< 1.8s = good, 1.8s-3s = needs improvement, > 3s = poor)
        if (vitals.fcp > 3000) score -= 20;
        else if (vitals.fcp > 1800) score -= 10;

        // CLS scoring (< 0.1 = good, 0.1-0.25 = needs improvement, > 0.25 = poor)
        if (vitals.cls > 0.25) score -= 25;
        else if (vitals.cls > 0.1) score -= 12;

        // FID scoring (< 100ms = good, 100ms-300ms = needs improvement, > 300ms = poor)
        if (vitals.fid > 300) score -= 15;
        else if (vitals.fid > 100) score -= 8;

        // TTFB scoring (< 800ms = good, 800ms-1800ms = needs improvement, > 1800ms = poor)
        if (vitals.ttfb > 1800) score -= 10;
        else if (vitals.ttfb > 800) score -= 5;

        this.metrics.performanceScore = Math.max(0, score);
        return this.metrics.performanceScore;
    }

    getResourceType(url) {
        if (url.includes('.js')) return 'script';
        if (url.includes('.css')) return 'stylesheet';
        if (url.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)) return 'image';
        if (url.includes('.woff') || url.includes('.ttf')) return 'font';
        if (url.includes('api/') || url.includes('.json')) return 'xhr';
        return 'other';
    }

    startMonitoring() {
        this.isMonitoring = true;
        
        // Monitor memory usage
        this.monitorMemory();
        
        // Monitor frame rate
        this.monitorFrameRate();
        
        // Monitor first input delay
        this.monitorFirstInputDelay();
        
        // Monitor JavaScript execution time
        this.monitorJSExecutionTime();
    }

    monitorMemory() {
        if (performance.memory) {
            const updateMemory = () => {
                this.metrics.memory = {
                    used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024 * 100) / 100,
                    total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024 * 100) / 100,
                    limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024 * 100) / 100,
                    usage: Math.round(performance.memory.usedJSHeapSize / performance.memory.jsHeapSizeLimit * 100),
                    timestamp: Date.now()
                };
            };

            updateMemory();
            setInterval(updateMemory, 5000); // Update every 5 seconds
        }
    }

    monitorFrameRate() {
        let frames = 0;
        let lastTime = performance.now();

        const countFrame = (currentTime) => {
            frames++;
            
            if (currentTime >= lastTime + 1000) {
                this.metrics.fps = {
                    current: Math.round(frames * 1000 / (currentTime - lastTime)),
                    timestamp: Date.now()
                };
                
                frames = 0;
                lastTime = currentTime;
            }
            
            if (this.isMonitoring) {
                requestAnimationFrame(countFrame);
            }
        };

        requestAnimationFrame(countFrame);
    }

    monitorFirstInputDelay() {
        let fidMeasured = false;
        
        const measureFID = (event) => {
            if (fidMeasured) return;
            
            const delay = performance.now() - event.timeStamp;
            this.metrics.fid = delay;
            fidMeasured = true;
            
            // Remove listeners after measuring
            ['click', 'keydown', 'mousedown', 'pointerdown', 'touchstart'].forEach(type => {
                document.removeEventListener(type, measureFID, true);
            });
            
            this.calculateCoreWebVitals();
        };

        // Listen for first input
        ['click', 'keydown', 'mousedown', 'pointerdown', 'touchstart'].forEach(type => {
            document.addEventListener(type, measureFID, { once: true, passive: true, capture: true });
        });
    }

    monitorJSExecutionTime() {
        let totalBlockingTime = 0;
        let longTaskCount = 0;

        if ('PerformanceObserver' in window) {
            try {
                const longTaskObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => {
                        if (entry.duration > 50) {
                            longTaskCount++;
                            totalBlockingTime += entry.duration - 50;
                        }
                    });
                    
                    this.metrics.longTasks = {
                        count: longTaskCount,
                        totalBlockingTime: totalBlockingTime,
                        timestamp: Date.now()
                    };
                });
                
                longTaskObserver.observe({ entryTypes: ['longtask'] });
                this.observers.push(longTaskObserver);
            } catch (e) {
                console.warn('Long task observer not supported');
            }
        }
    }

    createPerformanceWidget() {
        // Create floating performance widget
        const widget = document.createElement('div');
        widget.id = 'selinay-performance-widget';
        widget.innerHTML = `
            <div style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 15px;
                border-radius: 8px;
                font-family: monospace;
                font-size: 12px;
                z-index: 999;
                min-width: 200px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
            ">
                <div style="font-weight: bold; margin-bottom: 10px; color: #3b82f6;">
                    📊 Selinay Performance
                </div>
                <div id="perf-score">Score: --</div>
                <div id="perf-lcp">LCP: --</div>
                <div id="perf-fcp">FCP: --</div>
                <div id="perf-cls">CLS: --</div>
                <div id="perf-memory">Memory: --</div>
                <div id="perf-fps">FPS: --</div>
                <div style="margin-top: 10px;">
                    <button onclick="window.selinayPerf.toggleWidget()" style="
                        background: #3b82f6;
                        border: none;
                        color: white;
                        padding: 4px 8px;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 10px;
                    ">Hide</button>
                    <button onclick="window.selinayPerf.downloadReport()" style="
                        background: #10b981;
                        border: none;
                        color: white;
                        padding: 4px 8px;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 10px;
                        margin-left: 5px;
                    ">Report</button>
                </div>
            </div>
        `;

        document.body.appendChild(widget);
        this.widget = widget;
        this.updatePerformanceWidget();
    }

    updatePerformanceWidget() {
        if (!this.widget) return;

        const score = this.metrics.performanceScore || 0;
        const vitals = this.metrics.coreWebVitals || {};
        const memory = this.metrics.memory || {};
        const fps = this.metrics.fps || {};

        const scoreEl = this.widget.querySelector('#perf-score');
        const lcpEl = this.widget.querySelector('#perf-lcp');
        const fcpEl = this.widget.querySelector('#perf-fcp');
        const clsEl = this.widget.querySelector('#perf-cls');
        const memoryEl = this.widget.querySelector('#perf-memory');
        const fpsEl = this.widget.querySelector('#perf-fps');

        if (scoreEl) {
            scoreEl.textContent = `Score: ${score}`;
            scoreEl.style.color = score >= 90 ? '#10b981' : score >= 70 ? '#f59e0b' : '#ef4444';
        }

        if (lcpEl) lcpEl.textContent = `LCP: ${vitals.lcp ? Math.round(vitals.lcp) + 'ms' : '--'}`;
        if (fcpEl) fcpEl.textContent = `FCP: ${vitals.fcp ? Math.round(vitals.fcp) + 'ms' : '--'}`;
        if (clsEl) clsEl.textContent = `CLS: ${vitals.cls ? vitals.cls.toFixed(3) : '--'}`;
        if (memoryEl) memoryEl.textContent = `Memory: ${memory.used ? memory.used + 'MB' : '--'}`;
        if (fpsEl) fpsEl.textContent = `FPS: ${fps.current || '--'}`;
    }

    scheduleReports() {
        // Generate performance report every 30 seconds
        this.reportInterval = setInterval(() => {
            this.generatePerformanceReport();
        }, 30000);
    }

    generatePerformanceReport() {
        const report = {
            timestamp: new Date().toISOString(),
            url: window.location.href,
            userAgent: navigator.userAgent,
            score: this.metrics.performanceScore,
            coreWebVitals: this.metrics.coreWebVitals,
            memory: this.metrics.memory,
            fps: this.metrics.fps,
            navigation: this.metrics.navigation,
            resourceCount: this.metrics.resources ? this.metrics.resources.length : 0,
            longTasks: this.metrics.longTasks
        };

        // Store report in localStorage
        try {
            const reports = JSON.parse(localStorage.getItem('selinay-perf-reports') || '[]');
            reports.push(report);
            
            // Keep only last 50 reports
            if (reports.length > 50) {
                reports.splice(0, reports.length - 50);
            }
            
            localStorage.setItem('selinay-perf-reports', JSON.stringify(reports));
        } catch (e) {
            console.warn('Could not save performance report');
        }

        console.log('📊 Performance Report Generated:', report);
        return report;
    }

    // Public API methods
    getMetrics() {
        return this.metrics;
    }

    getScore() {
        return this.metrics.performanceScore || 0;
    }

    getCoreWebVitals() {
        return this.metrics.coreWebVitals || {};
    }

    toggleWidget() {
        if (this.widget) {
            const isHidden = this.widget.style.display === 'none';
            this.widget.style.display = isHidden ? 'block' : 'none';
        }
    }

    downloadReport() {
        const report = this.generatePerformanceReport();
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `selinay-performance-report-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    destroy() {
        this.isMonitoring = false;
        
        // Clear observers
        this.observers.forEach(observer => observer.disconnect());
        this.observers = [];
        
        // Clear interval
        if (this.reportInterval) {
            clearInterval(this.reportInterval);
        }
        
        // Remove widget
        if (this.widget && this.widget.parentNode) {
            this.widget.parentNode.removeChild(this.widget);
        }
    }
}

// Auto-initialize performance monitor
if (typeof window !== 'undefined') {
    // Wait for page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.selinayPerf = new SelinayPerformanceMonitor();
        });
    } else {
        window.selinayPerf = new SelinayPerformanceMonitor();
    }
    
    // Make class available globally
    window.SelinayPerformanceMonitor = SelinayPerformanceMonitor;
}

export default SelinayPerformanceMonitor;
