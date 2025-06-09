/**
 * SELİNAY TEAM - CROSS-BROWSER COMPATIBILITY TESTING
 * Task S-1: Phase 3 - Cross-Browser JavaScript Testing System
 * Created: 9 Haziran 2025
 * Target: 100% Modern Browser Support
 */

class MesChainCrossBrowserTester {
    constructor() {
        this.testResults = {
            browser: {},
            features: {},
            performance: {},
            compatibility: {},
            rendering: {}
        };
        
        this.supportedBrowsers = {
            chrome: { minVersion: 70, name: 'Chrome' },
            firefox: { minVersion: 65, name: 'Firefox' },
            safari: { minVersion: 12, name: 'Safari' },
            edge: { minVersion: 79, name: 'Edge' }
        };
        
        this.init();
    }

    /**
     * Initialize Cross-Browser Testing System
     */
    init() {
        console.log('🌐 MesChain Cross-Browser Tester Started');
        this.detectBrowser();
        this.testCSSFeatures();
        this.testJavaScriptFeatures();
        this.testRenderingEngine();
        this.testPerformanceAPIs();
        this.runCompatibilityTests();
        this.generateCompatibilityReport();
    }

    /**
     * Comprehensive Browser Detection
     */
    detectBrowser() {
        const userAgent = navigator.userAgent;
        const vendor = navigator.vendor;
        
        this.browser = {
            name: this.getBrowserName(userAgent, vendor),
            version: this.getBrowserVersion(userAgent),
            engine: this.getRenderingEngine(userAgent),
            platform: this.getPlatform(userAgent),
            mobile: this.isMobile(userAgent),
            supported: false,
            features: {}
        };

        // Check if browser is supported
        this.browser.supported = this.isBrowserSupported();
        
        console.log('🌐 Browser Detected:', this.browser);
        
        this.testResults.browser = this.browser;
        return this.browser;
    }

    /**
     * Get Browser Name
     */
    getBrowserName(userAgent, vendor) {
        if (/Chrome/i.test(userAgent) && /Google Inc/i.test(vendor)) {
            if (/Edg/i.test(userAgent)) return 'Edge';
            return 'Chrome';
        }
        if (/Firefox/i.test(userAgent)) return 'Firefox';
        if (/Safari/i.test(userAgent) && /Apple Computer/i.test(vendor)) return 'Safari';
        if (/Edge/i.test(userAgent)) return 'Edge';
        if (/Opera/i.test(userAgent)) return 'Opera';
        return 'Unknown';
    }

    /**
     * Get Browser Version
     */
    getBrowserVersion(userAgent) {
        let version = 'Unknown';
        
        if (/Chrome/i.test(userAgent)) {
            const match = userAgent.match(/Chrome\/(\d+)/);
            version = match ? parseInt(match[1]) : 'Unknown';
        } else if (/Firefox/i.test(userAgent)) {
            const match = userAgent.match(/Firefox\/(\d+)/);
            version = match ? parseInt(match[1]) : 'Unknown';
        } else if (/Safari/i.test(userAgent)) {
            const match = userAgent.match(/Version\/(\d+)/);
            version = match ? parseInt(match[1]) : 'Unknown';
        } else if (/Edge/i.test(userAgent)) {
            const match = userAgent.match(/Edge\/(\d+)/);
            version = match ? parseInt(match[1]) : 'Unknown';
        }
        
        return version;
    }

    /**
     * Get Rendering Engine
     */
    getRenderingEngine(userAgent) {
        if (/WebKit/i.test(userAgent)) {
            if (/Blink/i.test(userAgent)) return 'Blink';
            return 'WebKit';
        }
        if (/Gecko/i.test(userAgent)) return 'Gecko';
        if (/Trident/i.test(userAgent)) return 'Trident';
        if (/EdgeHTML/i.test(userAgent)) return 'EdgeHTML';
        return 'Unknown';
    }

    /**
     * Get Platform
     */
    getPlatform(userAgent) {
        if (/Windows/i.test(userAgent)) return 'Windows';
        if (/Mac/i.test(userAgent)) return 'macOS';
        if (/Linux/i.test(userAgent)) return 'Linux';
        if (/Android/i.test(userAgent)) return 'Android';
        if (/iPhone|iPad|iPod/i.test(userAgent)) return 'iOS';
        return 'Unknown';
    }

    /**
     * Check if Mobile
     */
    isMobile(userAgent) {
        return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent);
    }

    /**
     * Check if Browser is Supported
     */
    isBrowserSupported() {
        const browserName = this.browser.name.toLowerCase();
        const browserVersion = this.browser.version;
        
        if (this.supportedBrowsers[browserName]) {
            return browserVersion >= this.supportedBrowsers[browserName].minVersion;
        }
        
        return false;
    }

    /**
     * Test CSS Features Compatibility
     */
    testCSSFeatures() {
        console.log('🎨 Testing CSS Features...');
        
        const cssFeatures = {
            flexbox: this.testFlexboxSupport(),
            grid: this.testGridSupport(),
            transforms: this.testTransformSupport(),
            transitions: this.testTransitionSupport(),
            animations: this.testAnimationSupport(),
            gradients: this.testGradientSupport(),
            borderRadius: this.testBorderRadiusSupport(),
            boxShadow: this.testBoxShadowSupport(),
            customProperties: this.testCustomPropertiesSupport(),
            calc: this.testCalcSupport()
        };

        this.testResults.features.css = cssFeatures;
        
        const supportedFeatures = Object.values(cssFeatures).filter(Boolean).length;
        const totalFeatures = Object.keys(cssFeatures).length;
        const supportPercentage = (supportedFeatures / totalFeatures) * 100;
        
        console.log(`🎨 CSS Features Support: ${supportPercentage.toFixed(1)}% (${supportedFeatures}/${totalFeatures})`);
        
        return cssFeatures;
    }

    /**
     * Test Flexbox Support
     */
    testFlexboxSupport() {
        return CSS.supports('display', 'flex') || 
               CSS.supports('display', '-webkit-flex') ||
               CSS.supports('display', '-ms-flexbox');
    }

    /**
     * Test Grid Support
     */
    testGridSupport() {
        return CSS.supports('display', 'grid') ||
               CSS.supports('display', '-ms-grid');
    }

    /**
     * Test Transform Support
     */
    testTransformSupport() {
        return CSS.supports('transform', 'translateX(0)') ||
               CSS.supports('-webkit-transform', 'translateX(0)') ||
               CSS.supports('-moz-transform', 'translateX(0)') ||
               CSS.supports('-ms-transform', 'translateX(0)');
    }

    /**
     * Test Transition Support
     */
    testTransitionSupport() {
        return CSS.supports('transition', 'all 0.3s ease') ||
               CSS.supports('-webkit-transition', 'all 0.3s ease') ||
               CSS.supports('-moz-transition', 'all 0.3s ease') ||
               CSS.supports('-ms-transition', 'all 0.3s ease');
    }

    /**
     * Test Animation Support
     */
    testAnimationSupport() {
        return CSS.supports('animation', 'none') ||
               CSS.supports('-webkit-animation', 'none') ||
               CSS.supports('-moz-animation', 'none') ||
               CSS.supports('-ms-animation', 'none');
    }

    /**
     * Test Gradient Support
     */
    testGradientSupport() {
        return CSS.supports('background', 'linear-gradient(to right, red, blue)') ||
               CSS.supports('background', '-webkit-linear-gradient(left, red, blue)') ||
               CSS.supports('background', '-moz-linear-gradient(left, red, blue)') ||
               CSS.supports('background', '-ms-linear-gradient(left, red, blue)');
    }

    /**
     * Test Border Radius Support
     */
    testBorderRadiusSupport() {
        return CSS.supports('border-radius', '5px') ||
               CSS.supports('-webkit-border-radius', '5px') ||
               CSS.supports('-moz-border-radius', '5px');
    }

    /**
     * Test Box Shadow Support
     */
    testBoxShadowSupport() {
        return CSS.supports('box-shadow', '0 0 5px rgba(0,0,0,0.5)') ||
               CSS.supports('-webkit-box-shadow', '0 0 5px rgba(0,0,0,0.5)') ||
               CSS.supports('-moz-box-shadow', '0 0 5px rgba(0,0,0,0.5)');
    }

    /**
     * Test Custom Properties Support
     */
    testCustomPropertiesSupport() {
        return CSS.supports('--custom-property', 'value');
    }

    /**
     * Test Calc Support
     */
    testCalcSupport() {
        return CSS.supports('width', 'calc(100% - 20px)') ||
               CSS.supports('width', '-webkit-calc(100% - 20px)') ||
               CSS.supports('width', '-moz-calc(100% - 20px)');
    }

    /**
     * Test JavaScript Features Compatibility
     */
    testJavaScriptFeatures() {
        console.log('⚡ Testing JavaScript Features...');
        
        const jsFeatures = {
            es6: this.testES6Support(),
            promises: this.testPromiseSupport(),
            fetch: this.testFetchSupport(),
            localStorage: this.testLocalStorageSupport(),
            sessionStorage: this.testSessionStorageSupport(),
            webWorkers: this.testWebWorkerSupport(),
            serviceWorkers: this.testServiceWorkerSupport(),
            geolocation: this.testGeolocationSupport(),
            notifications: this.testNotificationSupport(),
            webGL: this.testWebGLSupport()
        };

        this.testResults.features.javascript = jsFeatures;
        
        const supportedFeatures = Object.values(jsFeatures).filter(Boolean).length;
        const totalFeatures = Object.keys(jsFeatures).length;
        const supportPercentage = (supportedFeatures / totalFeatures) * 100;
        
        console.log(`⚡ JavaScript Features Support: ${supportPercentage.toFixed(1)}% (${supportedFeatures}/${totalFeatures})`);
        
        return jsFeatures;
    }

    /**
     * Test ES6 Support
     */
    testES6Support() {
        try {
            return typeof Symbol !== 'undefined' &&
                   typeof Promise !== 'undefined' &&
                   typeof Map !== 'undefined' &&
                   typeof Set !== 'undefined';
        } catch (e) {
            return false;
        }
    }

    /**
     * Test Promise Support
     */
    testPromiseSupport() {
        return typeof Promise !== 'undefined';
    }

    /**
     * Test Fetch Support
     */
    testFetchSupport() {
        return typeof fetch !== 'undefined';
    }

    /**
     * Test Local Storage Support
     */
    testLocalStorageSupport() {
        try {
            return typeof localStorage !== 'undefined' && localStorage !== null;
        } catch (e) {
            return false;
        }
    }

    /**
     * Test Session Storage Support
     */
    testSessionStorageSupport() {
        try {
            return typeof sessionStorage !== 'undefined' && sessionStorage !== null;
        } catch (e) {
            return false;
        }
    }

    /**
     * Test Web Worker Support
     */
    testWebWorkerSupport() {
        return typeof Worker !== 'undefined';
    }

    /**
     * Test Service Worker Support
     */
    testServiceWorkerSupport() {
        return 'serviceWorker' in navigator;
    }

    /**
     * Test Geolocation Support
     */
    testGeolocationSupport() {
        return 'geolocation' in navigator;
    }

    /**
     * Test Notification Support
     */
    testNotificationSupport() {
        return 'Notification' in window;
    }

    /**
     * Test WebGL Support
     */
    testWebGLSupport() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            return !!gl;
        } catch (e) {
            return false;
        }
    }

    /**
     * Test Rendering Engine Compatibility
     */
    testRenderingEngine() {
        console.log('🖼️ Testing Rendering Engine...');
        
        const renderingTests = {
            engine: this.browser.engine,
            hardwareAcceleration: this.testHardwareAcceleration(),
            smoothScrolling: this.testSmoothScrolling(),
            subpixelRendering: this.testSubpixelRendering(),
            fontRendering: this.testFontRendering(),
            imageRendering: this.testImageRendering()
        };

        this.testResults.rendering = renderingTests;
        
        console.log('🖼️ Rendering Engine Tests:', renderingTests);
        
        return renderingTests;
    }

    /**
     * Test Hardware Acceleration
     */
    testHardwareAcceleration() {
        const testElement = document.createElement('div');
        testElement.style.transform = 'translateZ(0)';
        testElement.style.willChange = 'transform';
        
        document.body.appendChild(testElement);
        const computedStyle = window.getComputedStyle(testElement);
        const hasAcceleration = computedStyle.transform !== 'none';
        document.body.removeChild(testElement);
        
        return hasAcceleration;
    }

    /**
     * Test Smooth Scrolling
     */
    testSmoothScrolling() {
        return CSS.supports('scroll-behavior', 'smooth');
    }

    /**
     * Test Subpixel Rendering
     */
    testSubpixelRendering() {
        const testElement = document.createElement('div');
        testElement.style.width = '1.5px';
        testElement.style.height = '1.5px';
        
        document.body.appendChild(testElement);
        const rect = testElement.getBoundingClientRect();
        const hasSubpixel = rect.width !== Math.floor(rect.width);
        document.body.removeChild(testElement);
        
        return hasSubpixel;
    }

    /**
     * Test Font Rendering
     */
    testFontRendering() {
        return CSS.supports('-webkit-font-smoothing', 'antialiased') ||
               CSS.supports('-moz-osx-font-smoothing', 'grayscale') ||
               CSS.supports('font-smooth', 'always');
    }

    /**
     * Test Image Rendering
     */
    testImageRendering() {
        return CSS.supports('image-rendering', 'crisp-edges') ||
               CSS.supports('image-rendering', '-webkit-crisp-edges') ||
               CSS.supports('image-rendering', '-moz-crisp-edges');
    }

    /**
     * Test Performance APIs
     */
    testPerformanceAPIs() {
        console.log('⚡ Testing Performance APIs...');
        
        const performanceTests = {
            performanceAPI: 'performance' in window,
            performanceObserver: 'PerformanceObserver' in window,
            navigationTiming: 'performance' in window && 'navigation' in performance,
            resourceTiming: 'performance' in window && 'getEntriesByType' in performance,
            userTiming: 'performance' in window && 'mark' in performance,
            frameTimingAPI: 'requestAnimationFrame' in window,
            intersectionObserver: 'IntersectionObserver' in window,
            resizeObserver: 'ResizeObserver' in window
        };

        this.testResults.performance = performanceTests;
        
        const supportedAPIs = Object.values(performanceTests).filter(Boolean).length;
        const totalAPIs = Object.keys(performanceTests).length;
        const supportPercentage = (supportedAPIs / totalAPIs) * 100;
        
        console.log(`⚡ Performance APIs Support: ${supportPercentage.toFixed(1)}% (${supportedAPIs}/${totalAPIs})`);
        
        return performanceTests;
    }

    /**
     * Run Comprehensive Compatibility Tests
     */
    runCompatibilityTests() {
        console.log('🧪 Running Compatibility Tests...');
        
        const compatibilityTests = {
            viewport: this.testViewportCompatibility(),
            events: this.testEventCompatibility(),
            forms: this.testFormCompatibility(),
            media: this.testMediaCompatibility(),
            security: this.testSecurityCompatibility()
        };

        this.testResults.compatibility = compatibilityTests;
        
        console.log('🧪 Compatibility Tests:', compatibilityTests);
        
        return compatibilityTests;
    }

    /**
     * Test Viewport Compatibility
     */
    testViewportCompatibility() {
        return {
            viewportMeta: !!document.querySelector('meta[name="viewport"]'),
            devicePixelRatio: 'devicePixelRatio' in window,
            matchMedia: 'matchMedia' in window,
            orientationAPI: 'orientation' in window || 'screen' in window && 'orientation' in screen
        };
    }

    /**
     * Test Event Compatibility
     */
    testEventCompatibility() {
        return {
            addEventListener: 'addEventListener' in window,
            touchEvents: 'ontouchstart' in window,
            pointerEvents: 'onpointerdown' in window,
            wheelEvent: 'onwheel' in window,
            keyboardEvents: 'onkeydown' in window,
            mouseEvents: 'onmousedown' in window
        };
    }

    /**
     * Test Form Compatibility
     */
    testFormCompatibility() {
        const input = document.createElement('input');
        
        return {
            html5InputTypes: this.testHTML5InputTypes(input),
            formValidation: 'checkValidity' in input,
            placeholder: 'placeholder' in input,
            autofocus: 'autofocus' in input,
            autocomplete: 'autocomplete' in input
        };
    }

    /**
     * Test HTML5 Input Types
     */
    testHTML5InputTypes(input) {
        const inputTypes = ['email', 'url', 'number', 'range', 'date', 'time', 'color'];
        const supportedTypes = [];
        
        inputTypes.forEach(type => {
            input.setAttribute('type', type);
            if (input.type === type) {
                supportedTypes.push(type);
            }
        });
        
        return {
            supported: supportedTypes,
            count: supportedTypes.length,
            percentage: (supportedTypes.length / inputTypes.length) * 100
        };
    }

    /**
     * Test Media Compatibility
     */
    testMediaCompatibility() {
        const video = document.createElement('video');
        const audio = document.createElement('audio');
        
        return {
            videoElement: !!video.canPlayType,
            audioElement: !!audio.canPlayType,
            mp4Support: video.canPlayType('video/mp4') !== '',
            webmSupport: video.canPlayType('video/webm') !== '',
            oggSupport: video.canPlayType('video/ogg') !== '',
            mp3Support: audio.canPlayType('audio/mpeg') !== '',
            wavSupport: audio.canPlayType('audio/wav') !== ''
        };
    }

    /**
     * Test Security Compatibility
     */
    testSecurityCompatibility() {
        return {
            https: location.protocol === 'https:',
            csp: 'SecurityPolicyViolationEvent' in window,
            sri: 'integrity' in document.createElement('script'),
            cors: 'withCredentials' in new XMLHttpRequest(),
            mixedContent: location.protocol === 'https:' && !this.hasMixedContent()
        };
    }

    /**
     * Check for Mixed Content
     */
    hasMixedContent() {
        const resources = document.querySelectorAll('img, script, link, iframe');
        for (let resource of resources) {
            const src = resource.src || resource.href;
            if (src && src.startsWith('http:')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate Comprehensive Compatibility Report
     */
    generateCompatibilityReport() {
        console.log('📊 Generating Compatibility Report...');
        
        const report = {
            timestamp: new Date().toISOString(),
            browser: this.browser,
            scores: this.calculateCompatibilityScores(),
            recommendations: this.generateRecommendations(),
            testResults: this.testResults,
            summary: this.generateSummary()
        };

        console.log('📊 Cross-Browser Compatibility Report:', report);
        
        // Store report
        if (this.testResults.features.javascript.localStorage) {
            localStorage.setItem('meschain_compatibility_report', JSON.stringify(report));
        }
        
        // Display results in UI
        this.displayCompatibilityResults(report);
        
        return report;
    }

    /**
     * Calculate Compatibility Scores
     */
    calculateCompatibilityScores() {
        const scores = {
            css: this.calculateCSSScore(),
            javascript: this.calculateJavaScriptScore(),
            rendering: this.calculateRenderingScore(),
            performance: this.calculatePerformanceScore(),
            compatibility: this.calculateOverallCompatibilityScore()
        };

        scores.overall = Object.values(scores).reduce((sum, score) => sum + score, 0) / Object.keys(scores).length;

        return scores;
    }

    /**
     * Calculate CSS Score
     */
    calculateCSSScore() {
        const cssFeatures = this.testResults.features.css;
        const supportedFeatures = Object.values(cssFeatures).filter(Boolean).length;
        const totalFeatures = Object.keys(cssFeatures).length;
        return (supportedFeatures / totalFeatures) * 100;
    }

    /**
     * Calculate JavaScript Score
     */
    calculateJavaScriptScore() {
        const jsFeatures = this.testResults.features.javascript;
        const supportedFeatures = Object.values(jsFeatures).filter(Boolean).length;
        const totalFeatures = Object.keys(jsFeatures).length;
        return (supportedFeatures / totalFeatures) * 100;
    }

    /**
     * Calculate Rendering Score
     */
    calculateRenderingScore() {
        const renderingFeatures = this.testResults.rendering;
        const supportedFeatures = Object.values(renderingFeatures).filter(feature => 
            typeof feature === 'boolean' ? feature : true
        ).length;
        const totalFeatures = Object.keys(renderingFeatures).length;
        return (supportedFeatures / totalFeatures) * 100;
    }

    /**
     * Calculate Performance Score
     */
    calculatePerformanceScore() {
        const performanceFeatures = this.testResults.performance;
        const supportedFeatures = Object.values(performanceFeatures).filter(Boolean).length;
        const totalFeatures = Object.keys(performanceFeatures).length;
        return (supportedFeatures / totalFeatures) * 100;
    }

    /**
     * Calculate Overall Compatibility Score
     */
    calculateOverallCompatibilityScore() {
        const compatibilityFeatures = this.testResults.compatibility;
        let totalScore = 0;
        let totalTests = 0;

        Object.values(compatibilityFeatures).forEach(category => {
            if (typeof category === 'object') {
                const categoryValues = Object.values(category).filter(val => typeof val === 'boolean');
                const supportedInCategory = categoryValues.filter(Boolean).length;
                totalScore += (supportedInCategory / categoryValues.length) * 100;
                totalTests++;
            }
        });

        return totalTests > 0 ? totalScore / totalTests : 100;
    }

    /**
     * Generate Recommendations
     */
    generateRecommendations() {
        const recommendations = [];
        const scores = this.calculateCompatibilityScores();

        if (!this.browser.supported) {
            recommendations.push(`Browser ${this.browser.name} ${this.browser.version} is not officially supported. Please upgrade to a newer version.`);
        }

        if (scores.css < 90) {
            recommendations.push('Some CSS features are not supported. Consider using polyfills or fallbacks.');
        }

        if (scores.javascript < 90) {
            recommendations.push('Some JavaScript features are not supported. Consider using polyfills.');
        }

        if (scores.performance < 80) {
            recommendations.push('Performance APIs are limited. Consider alternative performance monitoring methods.');
        }

        if (!this.testResults.features.css.flexbox) {
            recommendations.push('Flexbox is not supported. Use float-based layouts as fallback.');
        }

        if (!this.testResults.features.css.grid) {
            recommendations.push('CSS Grid is not supported. Use flexbox or float-based layouts as fallback.');
        }

        if (!this.testResults.features.javascript.fetch) {
            recommendations.push('Fetch API is not supported. Use XMLHttpRequest or a polyfill.');
        }

        return recommendations;
    }

    /**
     * Generate Summary
     */
    generateSummary() {
        const scores = this.calculateCompatibilityScores();
        
        return {
            browserSupported: this.browser.supported,
            overallScore: scores.overall,
            criticalIssues: this.generateRecommendations().length,
            readyForProduction: scores.overall >= 95 && this.browser.supported,
            testsPassed: this.countPassedTests(),
            totalTests: this.countTotalTests()
        };
    }

    /**
     * Count Passed Tests
     */
    countPassedTests() {
        let passed = 0;
        
        // Count CSS features
        passed += Object.values(this.testResults.features.css).filter(Boolean).length;
        
        // Count JavaScript features
        passed += Object.values(this.testResults.features.javascript).filter(Boolean).length;
        
        // Count performance features
        passed += Object.values(this.testResults.performance).filter(Boolean).length;
        
        return passed;
    }

    /**
     * Count Total Tests
     */
    countTotalTests() {
        let total = 0;
        
        // Count CSS features
        total += Object.keys(this.testResults.features.css).length;
        
        // Count JavaScript features
        total += Object.keys(this.testResults.features.javascript).length;
        
        // Count performance features
        total += Object.keys(this.testResults.performance).length;
        
        return total;
    }

    /**
     * Display Compatibility Results in UI
     */
    displayCompatibilityResults(report) {
        // Create results container if it doesn't exist
        let resultsContainer = document.getElementById('meschain-compatibility-results');
        if (!resultsContainer) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'meschain-compatibility-results';
            resultsContainer.className = 'meschain-compatibility-results';
            document.body.appendChild(resultsContainer);
        }

        // Generate HTML for results
        const html = `
            <div class="meschain-compatibility-header">
                <h3>🌐 Cross-Browser Compatibility Report</h3>
                <div class="meschain-browser-info">
                    <strong>${report.browser.name} ${report.browser.version}</strong>
                    <span class="meschain-engine">${report.browser.engine}</span>
                    <span class="meschain-platform">${report.browser.platform}</span>
                </div>
            </div>
            
            <div class="meschain-compatibility-scores">
                <div class="meschain-score-item">
                    <span class="meschain-score-label">Overall</span>
                    <span class="meschain-score-value">${report.scores.overall.toFixed(1)}%</span>
                </div>
                <div class="meschain-score-item">
                    <span class="meschain-score-label">CSS</span>
                    <span class="meschain-score-value">${report.scores.css.toFixed(1)}%</span>
                </div>
                <div class="meschain-score-item">
                    <span class="meschain-score-label">JavaScript</span>
                    <span class="meschain-score-value">${report.scores.javascript.toFixed(1)}%</span>
                </div>
                <div class="meschain-score-item">
                    <span class="meschain-score-label">Performance</span>
                    <span class="meschain-score-value">${report.scores.performance.toFixed(1)}%</span>
                </div>
            </div>
            
            <div class="meschain-compatibility-summary">
                <div class="meschain-summary-item ${report.summary.browserSupported ? 'supported' : 'unsupported'}">
                    Browser Support: ${report.summary.browserSupported ? 'Supported' : 'Not Supported'}
                </div>
                <div class="meschain-summary-item ${report.summary.readyForProduction ? 'ready' : 'not-ready'}">
                    Production Ready: ${report.summary.readyForProduction ? 'Yes' : 'No'}
                </div>
                <div class="meschain-summary-item">
                    Tests Passed: ${report.summary.testsPassed}/${report.summary.totalTests}
                </div>
            </div>
            
            ${report.recommendations.length > 0 ? `
                <div class="meschain-recommendations">
                    <h4>Recommendations:</h4>
                    <ul>
                        ${report.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        `;

        resultsContainer.innerHTML = html;
    }
}

// Initialize Cross-Browser Tester when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.mesChainCrossBrowserTester = new MesChainCrossBrowserTester();
    
    console.log('🌐 Cross-Browser Compatibility Testing Completed');
});

/**
 * Export for use in other modules
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MesChainCrossBrowserTester;
} 