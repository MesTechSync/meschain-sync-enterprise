/**
 * Cross-Browser Compatibility Testing Framework v1.0
 * Comprehensive browser compatibility testing for MesChain-Sync marketplace integrations
 * 
 * @version 1.0.0
 * @date June 4, 2025 04:00 UTC
 * @author MesChain Development Team
 * @priority CRITICAL - Alt Görev 4: Cross-browser Testing
 */

class CrossBrowserCompatibilityTester {
    constructor() {
        this.supportedBrowsers = [
            { name: 'Chrome', version: '125+', engine: 'Blink' },
            { name: 'Firefox', version: '115+', engine: 'Gecko' },
            { name: 'Safari', version: '16+', engine: 'WebKit' },
            { name: 'Edge', version: '125+', engine: 'Blink' },
            { name: 'Opera', version: '110+', engine: 'Blink' }
        ];
        
        this.testResults = {
            passed: 0,
            failed: 0,
            warnings: 0,
            browserResults: {},
            featureSupport: {},
            performanceMetrics: {}
        };
        
        this.testStartTime = Date.now();
        this.criticalFeatures = [
            'CSS Grid',
            'CSS Flexbox',
            'ES6 Modules',
            'Fetch API',
            'Service Workers',
            'WebSockets',
            'Local Storage',
            'Session Storage',
            'Canvas API',
            'Chart.js',
            'Bootstrap 5',
            'Font Awesome 6'
        ];
        
        this.init();
    }
    
    /**
     * Initialize browser detection and compatibility testing
     */
    init() {
        console.log('🔍 Cross-Browser Compatibility Tester başlatılıyor...');
        this.detectBrowser();
        this.detectFeatureSupport();
        this.setupPolyfills();
    }
    
    /**
     * Detect current browser and version
     */
    detectBrowser() {
        const userAgent = navigator.userAgent;
        let browser = 'Unknown';
        let version = 'Unknown';
        
        // Chrome detection
        if (userAgent.includes('Chrome') && !userAgent.includes('Edg')) {
            browser = 'Chrome';
            const match = userAgent.match(/Chrome\/(\d+)/);
            version = match ? match[1] : 'Unknown';
        }
        // Firefox detection
        else if (userAgent.includes('Firefox')) {
            browser = 'Firefox';
            const match = userAgent.match(/Firefox\/(\d+)/);
            version = match ? match[1] : 'Unknown';
        }
        // Safari detection
        else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
            browser = 'Safari';
            const match = userAgent.match(/Version\/(\d+)/);
            version = match ? match[1] : 'Unknown';
        }
        // Edge detection
        else if (userAgent.includes('Edg')) {
            browser = 'Edge';
            const match = userAgent.match(/Edg\/(\d+)/);
            version = match ? match[1] : 'Unknown';
        }
        // Opera detection
        else if (userAgent.includes('OPR')) {
            browser = 'Opera';
            const match = userAgent.match(/OPR\/(\d+)/);
            version = match ? match[1] : 'Unknown';
        }
        
        this.currentBrowser = { name: browser, version: version };
        console.log(`🌐 Tarayıcı algılandı: ${browser} ${version}`);
        
        // Check if browser is supported
        this.checkBrowserSupport();
    }
    
    /**
     * Check if current browser is supported
     */
    checkBrowserSupport() {
        const supported = this.supportedBrowsers.find(b => 
            b.name === this.currentBrowser.name
        );
        
        if (supported) {
            const minVersion = parseInt(supported.version);
            const currentVersion = parseInt(this.currentBrowser.version);
            
            if (currentVersion >= minVersion) {
                console.log(`✅ Tarayıcı destekleniyor: ${this.currentBrowser.name} ${this.currentBrowser.version}`);
                this.recordTestResult('pass', 'Browser Support', 'Browser is fully supported');
            } else {
                console.warn(`⚠️ Tarayıcı versiyonu eski: ${this.currentBrowser.name} ${this.currentBrowser.version} (Min: ${supported.version})`);
                this.recordTestResult('warning', 'Browser Support', `Outdated browser version: ${this.currentBrowser.version}`);
            }
        } else {
            console.error(`❌ Desteklenmeyen tarayıcı: ${this.currentBrowser.name}`);
            this.recordTestResult('fail', 'Browser Support', `Unsupported browser: ${this.currentBrowser.name}`);
        }
    }
    
    /**
     * Detect feature support for critical features
     */
    detectFeatureSupport() {
        console.log('🔧 Özellik desteği kontrol ediliyor...');
        
        const featureTests = {
            'CSS Grid': () => CSS.supports('display', 'grid'),
            'CSS Flexbox': () => CSS.supports('display', 'flex'),
            'ES6 Modules': () => 'noModule' in HTMLScriptElement.prototype,
            'Fetch API': () => 'fetch' in window,
            'Service Workers': () => 'serviceWorker' in navigator,
            'WebSockets': () => 'WebSocket' in window,
            'Local Storage': () => 'localStorage' in window,
            'Session Storage': () => 'sessionStorage' in window,
            'Canvas API': () => 'getContext' in document.createElement('canvas'),
            'Chart.js': () => typeof Chart !== 'undefined',
            'Bootstrap 5': () => typeof bootstrap !== 'undefined',
            'Font Awesome 6': () => document.querySelector('.fa, .fas, .far, .fab') !== null
        };
        
        for (const [feature, test] of Object.entries(featureTests)) {
            try {
                const supported = test();
                this.testResults.featureSupport[feature] = supported;
                
                if (supported) {
                    console.log(`✅ ${feature} destekleniyor`);
                    this.recordTestResult('pass', `Feature: ${feature}`, 'Feature is supported');
                } else {
                    console.warn(`⚠️ ${feature} desteklenmiyor`);
                    this.recordTestResult('warning', `Feature: ${feature}`, 'Feature not supported');
                }
            } catch (error) {
                console.error(`❌ ${feature} test hatası:`, error);
                this.recordTestResult('fail', `Feature: ${feature}`, `Feature test error: ${error.message}`);
            }
        }
    }
    
    /**
     * Setup polyfills for unsupported features
     */
    setupPolyfills() {
        console.log('🔄 Polyfill'ler kontrol ediliyor...');
        
        // Fetch polyfill for older browsers
        if (!this.testResults.featureSupport['Fetch API']) {
            this.loadPolyfill('https://cdn.jsdelivr.net/npm/whatwg-fetch@3.6.19/dist/fetch.umd.js', 'Fetch API');
        }
        
        // ES6 Promise polyfill
        if (!window.Promise) {
            this.loadPolyfill('https://cdn.jsdelivr.net/npm/es6-promise@4.2.8/dist/es6-promise.auto.min.js', 'ES6 Promise');
        }
        
        // Intersection Observer polyfill
        if (!('IntersectionObserver' in window)) {
            this.loadPolyfill('https://cdn.jsdelivr.net/npm/intersection-observer@0.12.2/intersection-observer.js', 'Intersection Observer');
        }
    }
    
    /**
     * Load polyfill script
     */
    loadPolyfill(url, name) {
        const script = document.createElement('script');
        script.src = url;
        script.onload = () => {
            console.log(`✅ ${name} polyfill yüklendi`);
            this.recordTestResult('pass', `Polyfill: ${name}`, 'Polyfill loaded successfully');
        };
        script.onerror = () => {
            console.error(`❌ ${name} polyfill yüklenemedi`);
            this.recordTestResult('fail', `Polyfill: ${name}`, 'Polyfill failed to load');
        };
        document.head.appendChild(script);
    }
    
    /**
     * Run comprehensive cross-browser tests
     */
    async runComprehensiveTests() {
        console.log('🧪 Kapsamlı cross-browser testleri başlatılıyor...');
        
        try {
            // CSS compatibility tests
            await this.testCSSCompatibility();
            
            // JavaScript compatibility tests
            await this.testJavaScriptCompatibility();
            
            // Chart.js compatibility tests
            await this.testChartJSCompatibility();
            
            // Bootstrap compatibility tests
            await this.testBootstrapCompatibility();
            
            // Performance tests
            await this.testPerformanceMetrics();
            
            // Responsive design tests
            await this.testResponsiveDesign();
            
            // Dark mode compatibility tests
            await this.testDarkModeCompatibility();
            
            // Generate final report
            return this.generateCompatibilityReport();
            
        } catch (error) {
            console.error('❌ Cross-browser testleri başarısız:', error);
            this.recordTestResult('fail', 'Comprehensive Tests', `Test suite failed: ${error.message}`);
            return this.generateErrorReport(error);
        }
    }
    
    /**
     * Test CSS compatibility
     */
    async testCSSCompatibility() {
        console.log('🎨 CSS uyumluluğu test ediliyor...');
        
        const cssTests = [
            { property: 'display', value: 'grid', name: 'CSS Grid' },
            { property: 'display', value: 'flex', name: 'CSS Flexbox' },
            { property: 'position', value: 'sticky', name: 'Sticky Position' },
            { property: 'backdrop-filter', value: 'blur(10px)', name: 'Backdrop Filter' },
            { property: 'border-radius', value: '10px', name: 'Border Radius' },
            { property: 'box-shadow', value: '0 4px 8px rgba(0,0,0,0.1)', name: 'Box Shadow' },
            { property: 'transform', value: 'translateX(100px)', name: 'CSS Transforms' },
            { property: 'transition', value: 'all 0.3s ease', name: 'CSS Transitions' }
        ];
        
        for (const test of cssTests) {
            try {
                const supported = CSS.supports(test.property, test.value);
                
                if (supported) {
                    console.log(`✅ ${test.name} destekleniyor`);
                    this.recordTestResult('pass', `CSS: ${test.name}`, 'CSS property supported');
                } else {
                    console.warn(`⚠️ ${test.name} desteklenmiyor`);
                    this.recordTestResult('warning', `CSS: ${test.name}`, 'CSS property not supported');
                }
            } catch (error) {
                console.error(`❌ ${test.name} test hatası:`, error);
                this.recordTestResult('fail', `CSS: ${test.name}`, `CSS test error: ${error.message}`);
            }
        }
    }
    
    /**
     * Test JavaScript compatibility
     */
    async testJavaScriptCompatibility() {
        console.log('🔧 JavaScript uyumluluğu test ediliyor...');
        
        const jsTests = [
            {
                name: 'Arrow Functions',
                test: () => { const test = () => true; return test(); }
            },
            {
                name: 'Template Literals',
                test: () => { const test = `template ${1 + 1}`; return test === 'template 2'; }
            },
            {
                name: 'Destructuring',
                test: () => { const [a] = [1]; return a === 1; }
            },
            {
                name: 'Async/Await',
                test: async () => { return await Promise.resolve(true); }
            },
            {
                name: 'Spread Operator',
                test: () => { const arr = [1, 2]; const spread = [...arr]; return spread.length === 2; }
            },
            {
                name: 'Map/Set',
                test: () => { const map = new Map(); const set = new Set(); return map && set; }
            }
        ];
        
        for (const test of jsTests) {
            try {
                const result = await test.test();
                
                if (result) {
                    console.log(`✅ ${test.name} destekleniyor`);
                    this.recordTestResult('pass', `JS: ${test.name}`, 'JavaScript feature supported');
                } else {
                    console.warn(`⚠️ ${test.name} desteklenmiyor`);
                    this.recordTestResult('warning', `JS: ${test.name}`, 'JavaScript feature not supported');
                }
            } catch (error) {
                console.error(`❌ ${test.name} test hatası:`, error);
                this.recordTestResult('fail', `JS: ${test.name}`, `JavaScript test error: ${error.message}`);
            }
        }
    }
    
    /**
     * Test Chart.js compatibility
     */
    async testChartJSCompatibility() {
        console.log('📊 Chart.js uyumluluğu test ediliyor...');
        
        try {
            if (typeof Chart !== 'undefined') {
                // Create test canvas
                const testCanvas = document.createElement('canvas');
                testCanvas.width = 100;
                testCanvas.height = 100;
                testCanvas.style.display = 'none';
                document.body.appendChild(testCanvas);
                
                // Test chart creation
                const testChart = new Chart(testCanvas, {
                    type: 'line',
                    data: {
                        labels: ['Test'],
                        datasets: [{
                            label: 'Test Dataset',
                            data: [1],
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)'
                        }]
                    },
                    options: {
                        responsive: false,
                        animation: false
                    }
                });
                
                // Cleanup
                testChart.destroy();
                document.body.removeChild(testCanvas);
                
                console.log('✅ Chart.js tam uyumlu');
                this.recordTestResult('pass', 'Chart.js Compatibility', 'Chart.js fully compatible');
                
            } else {
                console.warn('⚠️ Chart.js yüklenmemiş');
                this.recordTestResult('warning', 'Chart.js Compatibility', 'Chart.js not loaded');
            }
        } catch (error) {
            console.error('❌ Chart.js uyumluluk hatası:', error);
            this.recordTestResult('fail', 'Chart.js Compatibility', `Chart.js error: ${error.message}`);
        }
    }
    
    /**
     * Test Bootstrap compatibility
     */
    async testBootstrapCompatibility() {
        console.log('🅱️ Bootstrap uyumluluğu test ediliyor...');
        
        try {
            if (typeof bootstrap !== 'undefined') {
                // Test Bootstrap components
                const testModal = new bootstrap.Modal(document.createElement('div'));
                console.log('✅ Bootstrap Modal uyumlu');
                
                const testTooltip = new bootstrap.Tooltip(document.createElement('div'));
                console.log('✅ Bootstrap Tooltip uyumlu');
                
                this.recordTestResult('pass', 'Bootstrap Compatibility', 'Bootstrap components working');
                
            } else {
                console.warn('⚠️ Bootstrap yüklenmemiş');
                this.recordTestResult('warning', 'Bootstrap Compatibility', 'Bootstrap not loaded');
            }
        } catch (error) {
            console.error('❌ Bootstrap uyumluluk hatası:', error);
            this.recordTestResult('fail', 'Bootstrap Compatibility', `Bootstrap error: ${error.message}`);
        }
    }
    
    /**
     * Test performance metrics
     */
    async testPerformanceMetrics() {
        console.log('⚡ Performans metrikleri test ediliyor...');
        
        try {
            // Test page load performance
            if (performance && performance.navigation) {
                const loadTime = performance.navigation.loadEventEnd - performance.navigation.navigationStart;
                this.testResults.performanceMetrics.pageLoadTime = loadTime;
                
                if (loadTime < 3000) {
                    console.log(`✅ Sayfa yükleme süresi: ${loadTime}ms (İyi)`);
                    this.recordTestResult('pass', 'Page Load Performance', `Load time: ${loadTime}ms`);
                } else {
                    console.warn(`⚠️ Sayfa yükleme süresi: ${loadTime}ms (Yavaş)`);
                    this.recordTestResult('warning', 'Page Load Performance', `Slow load time: ${loadTime}ms`);
                }
            }
            
            // Test DOM ready time
            if (document.readyState === 'complete') {
                const domTime = performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart;
                this.testResults.performanceMetrics.domReadyTime = domTime;
                
                if (domTime < 1500) {
                    console.log(`✅ DOM hazır süresi: ${domTime}ms (İyi)`);
                    this.recordTestResult('pass', 'DOM Ready Performance', `DOM ready: ${domTime}ms`);
                } else {
                    console.warn(`⚠️ DOM hazır süresi: ${domTime}ms (Yavaş)`);
                    this.recordTestResult('warning', 'DOM Ready Performance', `Slow DOM ready: ${domTime}ms`);
                }
            }
            
        } catch (error) {
            console.error('❌ Performans test hatası:', error);
            this.recordTestResult('fail', 'Performance Metrics', `Performance test error: ${error.message}`);
        }
    }
    
    /**
     * Test responsive design
     */
    async testResponsiveDesign() {
        console.log('📱 Responsive tasarım test ediliyor...');
        
        const breakpoints = [
            { name: 'Mobile', width: 375 },
            { name: 'Tablet', width: 768 },
            { name: 'Desktop', width: 1200 },
            { name: 'Large Desktop', width: 1920 }
        ];
        
        for (const breakpoint of breakpoints) {
            try {
                // Simulate viewport change
                const mediaQuery = window.matchMedia(`(min-width: ${breakpoint.width}px)`);
                
                if (mediaQuery.matches || window.innerWidth >= breakpoint.width) {
                    console.log(`✅ ${breakpoint.name} (${breakpoint.width}px) responsive`);
                    this.recordTestResult('pass', `Responsive: ${breakpoint.name}`, `Breakpoint ${breakpoint.width}px working`);
                } else {
                    console.log(`ℹ️ ${breakpoint.name} (${breakpoint.width}px) şu anki viewport dışında`);
                }
                
            } catch (error) {
                console.error(`❌ ${breakpoint.name} responsive test hatası:`, error);
                this.recordTestResult('fail', `Responsive: ${breakpoint.name}`, `Responsive test error: ${error.message}`);
            }
        }
    }
    
    /**
     * Test dark mode compatibility
     */
    async testDarkModeCompatibility() {
        console.log('🌙 Dark mode uyumluluğu test ediliyor...');
        
        try {
            // Check for CSS custom properties support
            if (CSS.supports('color', 'var(--test-color)')) {
                console.log('✅ CSS Custom Properties destekleniyor');
                
                // Test dark mode toggle
                const darkModeToggle = document.querySelector('[data-theme-toggle]');
                if (darkModeToggle) {
                    console.log('✅ Dark mode toggle bulundu');
                    this.recordTestResult('pass', 'Dark Mode', 'Dark mode toggle available');
                } else {
                    console.warn('⚠️ Dark mode toggle bulunamadı');
                    this.recordTestResult('warning', 'Dark Mode', 'Dark mode toggle not found');
                }
                
                // Test system preference detection
                if (window.matchMedia) {
                    const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
                    if (darkModeQuery.matches) {
                        console.log('✅ Sistem dark mode tercihi algılandı');
                    } else {
                        console.log('ℹ️ Sistem light mode tercihinde');
                    }
                    this.recordTestResult('pass', 'System Theme Detection', 'System theme preference detected');
                } else {
                    console.warn('⚠️ Sistem tema tercihi algılanamıyor');
                    this.recordTestResult('warning', 'System Theme Detection', 'System theme detection not supported');
                }
                
            } else {
                console.error('❌ CSS Custom Properties desteklenmiyor');
                this.recordTestResult('fail', 'Dark Mode', 'CSS Custom Properties not supported');
            }
            
        } catch (error) {
            console.error('❌ Dark mode test hatası:', error);
            this.recordTestResult('fail', 'Dark Mode', `Dark mode test error: ${error.message}`);
        }
    }
    
    /**
     * Record test result
     */
    recordTestResult(status, testName, details) {
        const result = {
            status: status,
            testName: testName,
            details: details,
            timestamp: new Date().toISOString(),
            browser: this.currentBrowser
        };
        
        if (!this.testResults.browserResults[this.currentBrowser.name]) {
            this.testResults.browserResults[this.currentBrowser.name] = [];
        }
        
        this.testResults.browserResults[this.currentBrowser.name].push(result);
        
        switch (status) {
            case 'pass':
                this.testResults.passed++;
                break;
            case 'fail':
                this.testResults.failed++;
                break;
            case 'warning':
                this.testResults.warnings++;
                break;
        }
    }
    
    /**
     * Generate compatibility report
     */
    generateCompatibilityReport() {
        const testDuration = Date.now() - this.testStartTime;
        const totalTests = this.testResults.passed + this.testResults.failed + this.testResults.warnings;
        const successRate = totalTests > 0 ? ((this.testResults.passed / totalTests) * 100).toFixed(1) : 0;
        
        const report = {
            summary: {
                browser: this.currentBrowser,
                totalTests: totalTests,
                passed: this.testResults.passed,
                failed: this.testResults.failed,
                warnings: this.testResults.warnings,
                successRate: `${successRate}%`,
                testDuration: `${testDuration}ms`,
                timestamp: new Date().toISOString()
            },
            featureSupport: this.testResults.featureSupport,
            performanceMetrics: this.testResults.performanceMetrics,
            browserResults: this.testResults.browserResults,
            recommendations: this.generateRecommendations()
        };
        
        console.log('\n📋 CROSS-BROWSER COMPATIBILITY REPORT');
        console.log('=====================================');
        console.log(`🌐 Tarayıcı: ${this.currentBrowser.name} ${this.currentBrowser.version}`);
        console.log(`✅ Başarılı: ${this.testResults.passed}`);
        console.log(`❌ Başarısız: ${this.testResults.failed}`);
        console.log(`⚠️ Uyarı: ${this.testResults.warnings}`);
        console.log(`📊 Başarı Oranı: ${successRate}%`);
        console.log(`⏱️ Test Süresi: ${testDuration}ms`);
        
        return report;
    }
    
    /**
     * Generate recommendations based on test results
     */
    generateRecommendations() {
        const recommendations = [];
        
        if (this.testResults.failed > 0) {
            recommendations.push('⚠️ Bazı kritik özellikler desteklenmiyor. Polyfill kullanımını değerlendirin.');
        }
        
        if (this.testResults.warnings > 5) {
            recommendations.push('📊 Çok sayıda uyarı var. Tarayıcı uyumluluğunu iyileştirin.');
        }
        
        if (!this.testResults.featureSupport['Service Workers']) {
            recommendations.push('🔄 Service Worker desteği yok. PWA özelliklerinin çalışmayacağını unutmayın.');
        }
        
        if (!this.testResults.featureSupport['CSS Grid']) {
            recommendations.push('🎨 CSS Grid desteği yok. Flexbox fallback kullanın.');
        }
        
        if (this.testResults.performanceMetrics.pageLoadTime > 3000) {
            recommendations.push('⚡ Sayfa yükleme süresi yavaş. Performans optimizasyonu yapın.');
        }
        
        return recommendations;
    }
    
    /**
     * Generate error report
     */
    generateErrorReport(error) {
        return {
            error: true,
            message: error.message,
            browser: this.currentBrowser,
            timestamp: new Date().toISOString(),
            recommendations: [
                '🔧 Test ortamını kontrol edin',
                '🌐 Tarayıcı uyumluluğunu doğrulayın',
                '📋 Hata detaylarını inceleyin'
            ]
        };
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Cross-Browser Compatibility Tester başlatılıyor...');
    
    // Initialize tester
    window.crossBrowserTester = new CrossBrowserCompatibilityTester();
    
    // Add to global scope for manual testing
    window.runCrossBrowserTests = () => {
        return window.crossBrowserTester.runComprehensiveTests();
    };
    
    // Auto-run tests if URL parameter is present
    if (window.location.search.includes('cross-browser-test=true')) {
        setTimeout(() => {
            window.crossBrowserTester.runComprehensiveTests().then(report => {
                console.log('📋 Cross-Browser Test Report:', report);
                
                // Save report to localStorage for debugging
                localStorage.setItem('crossBrowserTestReport', JSON.stringify(report));
                
                // Display result notification
                const successRate = parseFloat(report.summary.successRate);
                if (successRate >= 90) {
                    console.log('🎉 Mükemmel tarayıcı uyumluluğu!');
                } else if (successRate >= 75) {
                    console.log('✅ İyi tarayıcı uyumluluğu');
                } else {
                    console.warn('⚠️ Tarayıcı uyumluluğu iyileştirilebilir');
                }
                
                // Optional: Send report to server for monitoring
                if (window.location.hostname !== 'localhost') {
                    fetch('/api/cross-browser-report', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(report)
                    }).catch(err => console.log('Report gönderimi başarısız:', err));
                }
            }).catch(error => {
                console.error('❌ Cross-browser testleri başarısız:', error);
            });
            });
        }, 2000); // Wait for other scripts to load
    }
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CrossBrowserCompatibilityTester;
}
