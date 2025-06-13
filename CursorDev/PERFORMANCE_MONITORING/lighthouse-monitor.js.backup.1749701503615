// 📊 SELINAY LIGHTHOUSE PERFORMANCE MONITOR
// Real-time Performance Tracking & Optimization
// Created: June 5, 2025 06:00 UTC

class SelinayLighthouseMonitor {
    constructor() {
        this.metrics = {
            performance: 0,
            accessibility: 0,
            bestPractices: 0,
            seo: 0,
            pwa: 0
        };
        
        this.realTimeMetrics = {
            fps: 0,
            memoryUsage: 0,
            loadTime: 0,
            firstContentfulPaint: 0,
            largestContentfulPaint: 0,
            cumulativeLayoutShift: 0,
            firstInputDelay: 0
        };
        
        this.optimizationSuggestions = [];
        this.isMonitoring = false;
        
        this.init();
    }

    init() {
        this.setupPerformanceObserver();
        this.measureWebVitals();
        this.createMonitoringInterface();
        this.startRealtimeMonitoring();
        
        console.log('📊 Selinay Lighthouse Monitor initialized');
    }

    setupPerformanceObserver() {
        if ('PerformanceObserver' in window) {
            // Largest Contentful Paint
            const lcpObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const lastEntry = entries[entries.length - 1];
                this.realTimeMetrics.largestContentfulPaint = lastEntry.startTime;
                this.updateMetricsDisplay();
            });
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });

            // First Input Delay
            const fidObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    this.realTimeMetrics.firstInputDelay = entry.processingStart - entry.startTime;
                    this.updateMetricsDisplay();
                }
            });
            fidObserver.observe({ entryTypes: ['first-input'] });

            // Cumulative Layout Shift
            const clsObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    if (!entry.hadRecentInput) {
                        this.realTimeMetrics.cumulativeLayoutShift += entry.value;
                        this.updateMetricsDisplay();
                    }
                }
            });
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        }
    }

    measureWebVitals() {
        // First Contentful Paint
        const paintEntries = performance.getEntriesByType('paint');
        const fcpEntry = paintEntries.find(entry => entry.name === 'first-contentful-paint');
        if (fcpEntry) {
            this.realTimeMetrics.firstContentfulPaint = fcpEntry.startTime;
        }

        // Load Time
        window.addEventListener('load', () => {
            const navTiming = performance.getEntriesByType('navigation')[0];
            if (navTiming) {
                this.realTimeMetrics.loadTime = navTiming.loadEventEnd - navTiming.loadEventStart;
                this.calculateLighthouseScores();
            }
        });
    }

    createMonitoringInterface() {
        // Create monitoring panel for development
        if (window.location.hostname === 'localhost' || window.location.search.includes('monitor=true')) {
            const panel = document.createElement('div');
            panel.id = 'selinay-lighthouse-panel';
            panel.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 320px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 15px;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                font-size: 14px;
                z-index: 10001;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                backdrop-filter: blur(10px);
                transition: all 0.3s ease;
            `;
            
            panel.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 16px;">📊 Lighthouse Monitor</h3>
                    <button onclick="this.parentElement.parentElement.style.display='none'" style="
                        background: rgba(255,255,255,0.2);
                        border: none;
                        color: white;
                        border-radius: 50%;
                        width: 24px;
                        height: 24px;
                        cursor: pointer;
                        font-size: 12px;
                    ">×</button>
                </div>
                <div id="lighthouse-scores"></div>
                <div id="web-vitals" style="margin-top: 15px;"></div>
                <div style="margin-top: 15px;">
                    <button onclick="window.selinayLighthouse.runFullAudit()" style="
                        background: rgba(255,255,255,0.2);
                        border: 1px solid rgba(255,255,255,0.3);
                        color: white;
                        padding: 8px 12px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 12px;
                        margin-right: 8px;
                    ">Run Audit</button>
                    <button onclick="window.selinayLighthouse.exportReport()" style="
                        background: rgba(255,255,255,0.2);
                        border: 1px solid rgba(255,255,255,0.3);
                        color: white;
                        padding: 8px 12px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 12px;
                    ">Export</button>
                </div>
            `;
            
            document.body.appendChild(panel);
        }
    }

    startRealtimeMonitoring() {
        this.isMonitoring = true;
        
        // FPS monitoring
        this.monitorFPS();
        
        // Memory monitoring
        setInterval(() => {
            if (performance.memory) {
                this.realTimeMetrics.memoryUsage = performance.memory.usedJSHeapSize / 1048576; // MB
            }
            this.updateMetricsDisplay();
        }, 1000);
        
        // Performance monitoring
        setInterval(() => {
            this.calculateLighthouseScores();
        }, 5000);
    }

    monitorFPS() {
        let frameCount = 0;
        let lastTime = performance.now();
        
        const measureFPS = (currentTime) => {
            frameCount++;
            
            if (currentTime - lastTime >= 1000) {
                this.realTimeMetrics.fps = frameCount;
                frameCount = 0;
                lastTime = currentTime;
                this.updateMetricsDisplay();
            }
            
            if (this.isMonitoring) {
                requestAnimationFrame(measureFPS);
            }
        };
        
        requestAnimationFrame(measureFPS);
    }

    calculateLighthouseScores() {
        // Performance Score Calculation (simplified Lighthouse algorithm)
        let performanceScore = 100;
        
        // First Contentful Paint (max 15 points)
        if (this.realTimeMetrics.firstContentfulPaint > 3000) {
            performanceScore -= 15;
        } else if (this.realTimeMetrics.firstContentfulPaint > 1800) {
            performanceScore -= 8;
        }
        
        // Largest Contentful Paint (max 25 points)
        if (this.realTimeMetrics.largestContentfulPaint > 4000) {
            performanceScore -= 25;
        } else if (this.realTimeMetrics.largestContentfulPaint > 2500) {
            performanceScore -= 15;
        }
        
        // First Input Delay (max 15 points)
        if (this.realTimeMetrics.firstInputDelay > 300) {
            performanceScore -= 15;
        } else if (this.realTimeMetrics.firstInputDelay > 100) {
            performanceScore -= 8;
        }
        
        // Cumulative Layout Shift (max 15 points)
        if (this.realTimeMetrics.cumulativeLayoutShift > 0.25) {
            performanceScore -= 15;
        } else if (this.realTimeMetrics.cumulativeLayoutShift > 0.1) {
            performanceScore -= 8;
        }
        
        // Memory usage (max 10 points)
        if (this.realTimeMetrics.memoryUsage > 100) {
            performanceScore -= 10;
        } else if (this.realTimeMetrics.memoryUsage > 50) {
            performanceScore -= 5;
        }
        
        // FPS (max 20 points)
        if (this.realTimeMetrics.fps < 30) {
            performanceScore -= 20;
        } else if (this.realTimeMetrics.fps < 50) {
            performanceScore -= 10;
        }
        
        this.metrics.performance = Math.max(0, performanceScore);
        
        // Calculate other scores
        this.calculateAccessibilityScore();
        this.calculateBestPracticesScore();
        this.calculateSEOScore();
        this.calculatePWAScore();
        
        this.generateOptimizationSuggestions();
    }

    calculateAccessibilityScore() {
        let score = 100;
        
        // Check for alt attributes on images
        const images = document.querySelectorAll('img');
        const imagesWithoutAlt = Array.from(images).filter(img => !img.alt);
        if (imagesWithoutAlt.length > 0) {
            score -= Math.min(30, imagesWithoutAlt.length * 5);
        }
        
        // Check for form labels
        const inputs = document.querySelectorAll('input[type]:not([type="hidden"]), textarea, select');
        const inputsWithoutLabels = Array.from(inputs).filter(input => 
            !input.labels.length && !input.getAttribute('aria-label') && !input.getAttribute('aria-labelledby')
        );
        if (inputsWithoutLabels.length > 0) {
            score -= Math.min(25, inputsWithoutLabels.length * 5);
        }
        
        // Check for headings hierarchy
        const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
        if (headings.length === 0) {
            score -= 15;
        }
        
        // Check for focus indicators
        const focusableElements = document.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        // This is a simplified check - in real implementation, you'd test actual focus styles
        
        this.metrics.accessibility = Math.max(0, score);
    }

    calculateBestPracticesScore() {
        let score = 100;
        
        // HTTPS check
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            score -= 15;
        }
        
        // Console errors check
        const originalConsoleError = console.error;
        let errorCount = 0;
        console.error = (...args) => {
            errorCount++;
            originalConsoleError.apply(console, args);
        };
        
        // Check for deprecated APIs (simplified)
        if (typeof document.write !== 'undefined') {
            // This is just an example - real checks would be more comprehensive
        }
        
        // Check for proper DOCTYPE
        if (!document.doctype) {
            score -= 10;
        }
        
        // Check for charset meta tag
        const charsetMeta = document.querySelector('meta[charset], meta[http-equiv="Content-Type"]');
        if (!charsetMeta) {
            score -= 5;
        }
        
        this.metrics.bestPractices = Math.max(0, score);
    }

    calculateSEOScore() {
        let score = 100;
        
        // Title tag
        const titleTag = document.querySelector('title');
        if (!titleTag || titleTag.textContent.trim().length === 0) {
            score -= 20;
        } else if (titleTag.textContent.trim().length > 60) {
            score -= 5;
        }
        
        // Meta description
        const metaDescription = document.querySelector('meta[name="description"]');
        if (!metaDescription) {
            score -= 15;
        } else if (metaDescription.content.length > 160) {
            score -= 5;
        }
        
        // H1 tag
        const h1Tags = document.querySelectorAll('h1');
        if (h1Tags.length === 0) {
            score -= 15;
        } else if (h1Tags.length > 1) {
            score -= 5;
        }
        
        // Images with alt text
        const images = document.querySelectorAll('img');
        const imagesWithoutAlt = Array.from(images).filter(img => !img.alt);
        if (imagesWithoutAlt.length > 0) {
            score -= Math.min(20, imagesWithoutAlt.length * 3);
        }
        
        // Links with descriptive text
        const links = document.querySelectorAll('a[href]');
        const emptyLinks = Array.from(links).filter(link => 
            link.textContent.trim().length === 0 && !link.querySelector('img[alt]')
        );
        if (emptyLinks.length > 0) {
            score -= Math.min(15, emptyLinks.length * 2);
        }
        
        this.metrics.seo = Math.max(0, score);
    }

    calculatePWAScore() {
        let score = 0;
        
        // Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(registrations => {
                if (registrations.length > 0) {
                    score += 30;
                }
            });
        }
        
        // Web App Manifest
        const manifestLink = document.querySelector('link[rel="manifest"]');
        if (manifestLink) {
            score += 25;
        }
        
        // Viewport meta tag
        const viewportMeta = document.querySelector('meta[name="viewport"]');
        if (viewportMeta) {
            score += 15;
        }
        
        // Theme color
        const themeColor = document.querySelector('meta[name="theme-color"]');
        if (themeColor) {
            score += 10;
        }
        
        // HTTPS
        if (location.protocol === 'https:' || location.hostname === 'localhost') {
            score += 20;
        }
        
        this.metrics.pwa = Math.min(100, score);
    }

    generateOptimizationSuggestions() {
        this.optimizationSuggestions = [];
        
        // Performance suggestions
        if (this.metrics.performance < 90) {
            if (this.realTimeMetrics.firstContentfulPaint > 1800) {
                this.optimizationSuggestions.push({
                    category: 'Performance',
                    priority: 'High',
                    issue: 'First Contentful Paint is slow',
                    suggestion: 'Optimize critical rendering path, minimize render-blocking resources',
                    impact: 'High'
                });
            }
            
            if (this.realTimeMetrics.largestContentfulPaint > 2500) {
                this.optimizationSuggestions.push({
                    category: 'Performance',
                    priority: 'High',
                    issue: 'Largest Contentful Paint is slow',
                    suggestion: 'Optimize images, preload important resources, use CDN',
                    impact: 'High'
                });
            }
            
            if (this.realTimeMetrics.cumulativeLayoutShift > 0.1) {
                this.optimizationSuggestions.push({
                    category: 'Performance',
                    priority: 'Medium',
                    issue: 'Layout shifts detected',
                    suggestion: 'Add size attributes to images, reserve space for dynamic content',
                    impact: 'Medium'
                });
            }
            
            if (this.realTimeMetrics.memoryUsage > 50) {
                this.optimizationSuggestions.push({
                    category: 'Performance',
                    priority: 'Medium',
                    issue: 'High memory usage',
                    suggestion: 'Optimize JavaScript, clean up event listeners, use lazy loading',
                    impact: 'Medium'
                });
            }
        }
        
        // Accessibility suggestions
        if (this.metrics.accessibility < 90) {
            const imagesWithoutAlt = document.querySelectorAll('img:not([alt])');
            if (imagesWithoutAlt.length > 0) {
                this.optimizationSuggestions.push({
                    category: 'Accessibility',
                    priority: 'High',
                    issue: `${imagesWithoutAlt.length} images without alt text`,
                    suggestion: 'Add descriptive alt attributes to all images',
                    impact: 'High'
                });
            }
        }
        
        // SEO suggestions
        if (this.metrics.seo < 90) {
            if (!document.querySelector('title') || document.title.trim().length === 0) {
                this.optimizationSuggestions.push({
                    category: 'SEO',
                    priority: 'High',
                    issue: 'Missing or empty title tag',
                    suggestion: 'Add a descriptive title tag (50-60 characters)',
                    impact: 'High'
                });
            }
            
            if (!document.querySelector('meta[name="description"]')) {
                this.optimizationSuggestions.push({
                    category: 'SEO',
                    priority: 'High',
                    issue: 'Missing meta description',
                    suggestion: 'Add a meta description (150-160 characters)',
                    impact: 'High'
                });
            }
        }
    }

    updateMetricsDisplay() {
        const scoresDiv = document.getElementById('lighthouse-scores');
        const vitalsDiv = document.getElementById('web-vitals');
        
        if (scoresDiv) {
            scoresDiv.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                    <div>🚀 Performance: <strong>${Math.round(this.metrics.performance)}</strong></div>
                    <div>♿ Accessibility: <strong>${Math.round(this.metrics.accessibility)}</strong></div>
                    <div>✅ Best Practices: <strong>${Math.round(this.metrics.bestPractices)}</strong></div>
                    <div>🔍 SEO: <strong>${Math.round(this.metrics.seo)}</strong></div>
                    <div colspan="2">📱 PWA: <strong>${Math.round(this.metrics.pwa)}</strong></div>
                </div>
            `;
        }
        
        if (vitalsDiv) {
            vitalsDiv.innerHTML = `
                <div style="font-size: 11px; opacity: 0.9;">
                    <div>FCP: ${Math.round(this.realTimeMetrics.firstContentfulPaint)}ms</div>
                    <div>LCP: ${Math.round(this.realTimeMetrics.largestContentfulPaint)}ms</div>
                    <div>CLS: ${this.realTimeMetrics.cumulativeLayoutShift.toFixed(3)}</div>
                    <div>FID: ${Math.round(this.realTimeMetrics.firstInputDelay)}ms</div>
                    <div>FPS: ${this.realTimeMetrics.fps} | Memory: ${Math.round(this.realTimeMetrics.memoryUsage)}MB</div>
                </div>
            `;
        }
    }

    async runFullAudit() {
        console.log('🔍 Running full Lighthouse audit...');
        
        // Recalculate all scores
        this.calculateLighthouseScores();
        
        // Update display
        this.updateMetricsDisplay();
        
        // Generate report
        const report = this.generateReport();
        
        console.log('📊 Audit complete:', report);
        return report;
    }

    generateReport() {
        const overallScore = Math.round(
            (this.metrics.performance + this.metrics.accessibility + 
             this.metrics.bestPractices + this.metrics.seo + this.metrics.pwa) / 5
        );
        
        return {
            timestamp: new Date().toISOString(),
            url: window.location.href,
            overallScore,
            scores: { ...this.metrics },
            webVitals: { ...this.realTimeMetrics },
            optimizationSuggestions: [...this.optimizationSuggestions],
            browserInfo: {
                userAgent: navigator.userAgent,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            }
        };
    }

    exportReport() {
        const report = this.generateReport();
        const dataStr = JSON.stringify(report, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `selinay-lighthouse-report-${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        
        console.log('📥 Report exported successfully');
    }

    getRecommendations() {
        return this.optimizationSuggestions.sort((a, b) => {
            const priorityOrder = { 'High': 3, 'Medium': 2, 'Low': 1 };
            return priorityOrder[b.priority] - priorityOrder[a.priority];
        });
    }

    stop() {
        this.isMonitoring = false;
        console.log('📊 Lighthouse monitoring stopped');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.selinayLighthouse = new SelinayLighthouseMonitor();
    
    // Auto-run audit if URL parameter is present
    if (window.location.search.includes('audit=true')) {
        setTimeout(() => {
            window.selinayLighthouse.runFullAudit();
        }, 3000);
    }
    
    console.log('📊 Selinay Lighthouse Monitor ready!');
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SelinayLighthouseMonitor;
}
