/**
 * SELİNAY TEAM - MOBILE RESPONSIVENESS VALIDATION
 * Task S-1: Phase 2 - Mobile Responsive JavaScript Testing System
 * Created: 9 Haziran 2025
 * Target: 99.9% Mobile Compatibility
 */

class MesChainMobileValidator {
    constructor() {
        this.testResults = {
            viewport: {},
            touchInterface: {},
            performance: {},
            compatibility: {},
            accessibility: {}
        };
        
        this.breakpoints = {
            mobile: { min: 320, max: 767 },
            tablet: { min: 768, max: 1023 },
            desktop: { min: 1024, max: Infinity }
        };
        
        this.init();
    }

    /**
     * Initialize Mobile Validation System
     */
    init() {
        console.log('🚀 MesChain Mobile Validator Started');
        this.detectDevice();
        this.setupViewportTesting();
        this.setupTouchInterface();
        this.setupPerformanceMonitoring();
        this.runCompatibilityTests();
        this.validateAccessibility();
    }

    /**
     * Device Detection and Classification
     */
    detectDevice() {
        const userAgent = navigator.userAgent;
        const viewport = {
            width: window.innerWidth,
            height: window.innerHeight,
            ratio: window.devicePixelRatio || 1
        };

        this.device = {
            type: this.getDeviceType(viewport.width),
            os: this.getOperatingSystem(userAgent),
            browser: this.getBrowser(userAgent),
            viewport: viewport,
            touchSupport: 'ontouchstart' in window,
            orientation: this.getOrientation()
        };

        console.log('📱 Device Detected:', this.device);
        return this.device;
    }

    /**
     * Get Device Type Based on Viewport Width
     */
    getDeviceType(width) {
        if (width <= 767) return 'mobile';
        if (width <= 1023) return 'tablet';
        return 'desktop';
    }

    /**
     * Get Operating System
     */
    getOperatingSystem(userAgent) {
        if (/Android/i.test(userAgent)) return 'Android';
        if (/iPhone|iPad|iPod/i.test(userAgent)) return 'iOS';
        if (/Windows/i.test(userAgent)) return 'Windows';
        if (/Mac/i.test(userAgent)) return 'macOS';
        if (/Linux/i.test(userAgent)) return 'Linux';
        return 'Unknown';
    }

    /**
     * Get Browser Information
     */
    getBrowser(userAgent) {
        if (/Chrome/i.test(userAgent) && !/Edge/i.test(userAgent)) return 'Chrome';
        if (/Firefox/i.test(userAgent)) return 'Firefox';
        if (/Safari/i.test(userAgent) && !/Chrome/i.test(userAgent)) return 'Safari';
        if (/Edge/i.test(userAgent)) return 'Edge';
        return 'Unknown';
    }

    /**
     * Get Device Orientation
     */
    getOrientation() {
        return window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';
    }

    /**
     * Viewport Testing System
     */
    setupViewportTesting() {
        console.log('🔍 Starting Viewport Testing...');
        
        // Test all breakpoints
        Object.keys(this.breakpoints).forEach(breakpoint => {
            this.testBreakpoint(breakpoint);
        });

        // Listen for resize events
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.handleViewportChange();
            }, 250);
        });

        // Listen for orientation changes
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleOrientationChange();
            }, 500);
        });
    }

    /**
     * Test Specific Breakpoint
     */
    testBreakpoint(breakpoint) {
        const bp = this.breakpoints[breakpoint];
        const currentWidth = window.innerWidth;
        
        const isActive = currentWidth >= bp.min && currentWidth <= bp.max;
        
        this.testResults.viewport[breakpoint] = {
            active: isActive,
            width: currentWidth,
            elements: this.testElementsAtBreakpoint(breakpoint),
            layout: this.testLayoutAtBreakpoint(breakpoint),
            performance: this.testPerformanceAtBreakpoint(breakpoint)
        };

        if (isActive) {
            console.log(`✅ ${breakpoint} breakpoint active (${currentWidth}px)`);
            this.applyBreakpointStyles(breakpoint);
        }
    }

    /**
     * Test Elements at Specific Breakpoint
     */
    testElementsAtBreakpoint(breakpoint) {
        const elements = {
            navigation: this.testNavigationElements(),
            buttons: this.testButtonElements(),
            inputs: this.testInputElements(),
            cards: this.testCardElements(),
            images: this.testImageElements()
        };

        return elements;
    }

    /**
     * Test Navigation Elements
     */
    testNavigationElements() {
        const navElements = document.querySelectorAll('.meschain-nav, .meschain-mobile-nav');
        const results = [];

        navElements.forEach(nav => {
            const rect = nav.getBoundingClientRect();
            results.push({
                element: nav.className,
                visible: rect.width > 0 && rect.height > 0,
                dimensions: { width: rect.width, height: rect.height },
                touchFriendly: rect.height >= 44
            });
        });

        return results;
    }

    /**
     * Test Button Elements
     */
    testButtonElements() {
        const buttons = document.querySelectorAll('.meschain-button, .meschain-touch-target');
        const results = [];

        buttons.forEach(button => {
            const rect = button.getBoundingClientRect();
            const isAccessible = rect.width >= 44 && rect.height >= 44;
            
            results.push({
                element: button.className,
                accessible: isAccessible,
                dimensions: { width: rect.width, height: rect.height },
                touchTarget: isAccessible
            });
        });

        return results;
    }

    /**
     * Test Input Elements
     */
    testInputElements() {
        const inputs = document.querySelectorAll('.meschain-input, input, textarea');
        const results = [];

        inputs.forEach(input => {
            const rect = input.getBoundingClientRect();
            results.push({
                element: input.tagName.toLowerCase(),
                dimensions: { width: rect.width, height: rect.height },
                fontSize: window.getComputedStyle(input).fontSize,
                touchFriendly: rect.height >= 44
            });
        });

        return results;
    }

    /**
     * Touch Interface Optimization
     */
    setupTouchInterface() {
        console.log('👆 Setting up Touch Interface...');

        if (!this.device.touchSupport) {
            console.log('⚠️ Touch not supported on this device');
            return;
        }

        this.setupTouchEvents();
        this.setupGestureSupport();
        this.validateTouchTargets();
    }

    /**
     * Setup Touch Events
     */
    setupTouchEvents() {
        let touchStartTime = 0;
        let touchStartPos = { x: 0, y: 0 };

        document.addEventListener('touchstart', (e) => {
            touchStartTime = Date.now();
            touchStartPos = {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY
            };
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            const touchEndTime = Date.now();
            const touchDuration = touchEndTime - touchStartTime;
            
            // Measure touch response time
            this.testResults.touchInterface.responseTime = touchDuration;
            
            if (touchDuration < 100) {
                console.log('✅ Touch response time optimal:', touchDuration + 'ms');
            } else {
                console.log('⚠️ Touch response time slow:', touchDuration + 'ms');
            }
        }, { passive: true });
    }

    /**
     * Setup Gesture Support
     */
    setupGestureSupport() {
        let startX = 0;
        let startY = 0;
        let distX = 0;
        let distY = 0;

        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (!startX || !startY) return;

            distX = e.touches[0].clientX - startX;
            distY = e.touches[0].clientY - startY;

            // Detect swipe gestures
            if (Math.abs(distX) > Math.abs(distY)) {
                if (Math.abs(distX) > 50) {
                    const direction = distX > 0 ? 'right' : 'left';
                    this.handleSwipeGesture(direction);
                }
            }
        }, { passive: true });
    }

    /**
     * Handle Swipe Gestures
     */
    handleSwipeGesture(direction) {
        console.log('👆 Swipe detected:', direction);
        
        // Handle mobile navigation swipe
        const mobileNav = document.querySelector('.meschain-mobile-nav');
        if (mobileNav) {
            if (direction === 'right' && !mobileNav.classList.contains('active')) {
                this.openMobileNav();
            } else if (direction === 'left' && mobileNav.classList.contains('active')) {
                this.closeMobileNav();
            }
        }
    }

    /**
     * Validate Touch Targets
     */
    validateTouchTargets() {
        const touchTargets = document.querySelectorAll('button, a, input, .meschain-touch-target');
        let validTargets = 0;
        let totalTargets = touchTargets.length;

        touchTargets.forEach(target => {
            const rect = target.getBoundingClientRect();
            const isValid = rect.width >= 44 && rect.height >= 44;
            
            if (isValid) {
                validTargets++;
                target.classList.add('meschain-touch-valid');
            } else {
                target.classList.add('meschain-touch-invalid');
                console.warn('⚠️ Touch target too small:', target, rect);
            }
        });

        const validationScore = (validTargets / totalTargets) * 100;
        this.testResults.touchInterface.validationScore = validationScore;
        
        console.log(`👆 Touch Target Validation: ${validationScore.toFixed(1)}% (${validTargets}/${totalTargets})`);
    }

    /**
     * Performance Monitoring
     */
    setupPerformanceMonitoring() {
        console.log('⚡ Setting up Performance Monitoring...');

        // Monitor loading performance
        this.monitorLoadingPerformance();
        
        // Monitor runtime performance
        this.monitorRuntimePerformance();
        
        // Monitor memory usage
        this.monitorMemoryUsage();
        
        // Monitor battery usage (if available)
        this.monitorBatteryUsage();
    }

    /**
     * Monitor Loading Performance
     */
    monitorLoadingPerformance() {
        if ('performance' in window) {
            const navigation = performance.getEntriesByType('navigation')[0];
            
            this.testResults.performance.loading = {
                domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                totalTime: navigation.loadEventEnd - navigation.navigationStart,
                firstPaint: this.getFirstPaint(),
                firstContentfulPaint: this.getFirstContentfulPaint()
            };

            console.log('⚡ Loading Performance:', this.testResults.performance.loading);
        }
    }

    /**
     * Get First Paint Time
     */
    getFirstPaint() {
        const paintEntries = performance.getEntriesByType('paint');
        const firstPaint = paintEntries.find(entry => entry.name === 'first-paint');
        return firstPaint ? firstPaint.startTime : 0;
    }

    /**
     * Get First Contentful Paint Time
     */
    getFirstContentfulPaint() {
        const paintEntries = performance.getEntriesByType('paint');
        const firstContentfulPaint = paintEntries.find(entry => entry.name === 'first-contentful-paint');
        return firstContentfulPaint ? firstContentfulPaint.startTime : 0;
    }

    /**
     * Monitor Runtime Performance
     */
    monitorRuntimePerformance() {
        let frameCount = 0;
        let lastTime = performance.now();
        let fps = 0;

        const measureFPS = (currentTime) => {
            frameCount++;
            
            if (currentTime - lastTime >= 1000) {
                fps = Math.round((frameCount * 1000) / (currentTime - lastTime));
                frameCount = 0;
                lastTime = currentTime;
                
                this.testResults.performance.fps = fps;
                
                if (fps < 30) {
                    console.warn('⚠️ Low FPS detected:', fps);
                } else if (fps >= 60) {
                    console.log('✅ Optimal FPS:', fps);
                }
            }
            
            requestAnimationFrame(measureFPS);
        };

        requestAnimationFrame(measureFPS);
    }

    /**
     * Monitor Memory Usage
     */
    monitorMemoryUsage() {
        if ('memory' in performance) {
            const memory = performance.memory;
            
            this.testResults.performance.memory = {
                used: Math.round(memory.usedJSHeapSize / 1048576), // MB
                total: Math.round(memory.totalJSHeapSize / 1048576), // MB
                limit: Math.round(memory.jsHeapSizeLimit / 1048576) // MB
            };

            console.log('💾 Memory Usage:', this.testResults.performance.memory);
        }
    }

    /**
     * Monitor Battery Usage
     */
    async monitorBatteryUsage() {
        if ('getBattery' in navigator) {
            try {
                const battery = await navigator.getBattery();
                
                this.testResults.performance.battery = {
                    level: Math.round(battery.level * 100),
                    charging: battery.charging,
                    chargingTime: battery.chargingTime,
                    dischargingTime: battery.dischargingTime
                };

                console.log('🔋 Battery Status:', this.testResults.performance.battery);
            } catch (error) {
                console.log('⚠️ Battery API not available');
            }
        }
    }

    /**
     * Run Compatibility Tests
     */
    runCompatibilityTests() {
        console.log('🌐 Running Compatibility Tests...');

        this.testResults.compatibility = {
            css: this.testCSSSupport(),
            javascript: this.testJavaScriptSupport(),
            html5: this.testHTML5Support(),
            webgl: this.testWebGLSupport(),
            serviceWorker: this.testServiceWorkerSupport()
        };

        console.log('🌐 Compatibility Results:', this.testResults.compatibility);
    }

    /**
     * Test CSS Support
     */
    testCSSSupport() {
        const features = {
            flexbox: CSS.supports('display', 'flex'),
            grid: CSS.supports('display', 'grid'),
            transforms: CSS.supports('transform', 'translateZ(0)'),
            animations: CSS.supports('animation', 'none'),
            customProperties: CSS.supports('--custom', 'value')
        };

        return features;
    }

    /**
     * Test JavaScript Support
     */
    testJavaScriptSupport() {
        const features = {
            es6: typeof Symbol !== 'undefined',
            promises: typeof Promise !== 'undefined',
            fetch: typeof fetch !== 'undefined',
            localStorage: typeof localStorage !== 'undefined',
            sessionStorage: typeof sessionStorage !== 'undefined'
        };

        return features;
    }

    /**
     * Test HTML5 Support
     */
    testHTML5Support() {
        const features = {
            canvas: !!document.createElement('canvas').getContext,
            video: !!document.createElement('video').canPlayType,
            audio: !!document.createElement('audio').canPlayType,
            geolocation: 'geolocation' in navigator,
            webWorkers: typeof Worker !== 'undefined'
        };

        return features;
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
     * Test Service Worker Support
     */
    testServiceWorkerSupport() {
        return 'serviceWorker' in navigator;
    }

    /**
     * Validate Accessibility
     */
    validateAccessibility() {
        console.log('♿ Validating Accessibility...');

        this.testResults.accessibility = {
            ariaLabels: this.checkAriaLabels(),
            colorContrast: this.checkColorContrast(),
            keyboardNavigation: this.checkKeyboardNavigation(),
            focusManagement: this.checkFocusManagement(),
            semanticHTML: this.checkSemanticHTML()
        };

        console.log('♿ Accessibility Results:', this.testResults.accessibility);
    }

    /**
     * Check ARIA Labels
     */
    checkAriaLabels() {
        const elementsNeedingLabels = document.querySelectorAll('button, input, a, [role="button"]');
        let labeledElements = 0;

        elementsNeedingLabels.forEach(element => {
            if (element.getAttribute('aria-label') || 
                element.getAttribute('aria-labelledby') || 
                element.textContent.trim() ||
                element.querySelector('img[alt]')) {
                labeledElements++;
            }
        });

        return {
            total: elementsNeedingLabels.length,
            labeled: labeledElements,
            score: (labeledElements / elementsNeedingLabels.length) * 100
        };
    }

    /**
     * Check Color Contrast
     */
    checkColorContrast() {
        // Simplified contrast check
        const textElements = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, a, button');
        let contrastIssues = 0;

        textElements.forEach(element => {
            const styles = window.getComputedStyle(element);
            const color = styles.color;
            const backgroundColor = styles.backgroundColor;
            
            // This is a simplified check - in production, you'd use a proper contrast calculation
            if (color === backgroundColor) {
                contrastIssues++;
            }
        });

        return {
            total: textElements.length,
            issues: contrastIssues,
            score: ((textElements.length - contrastIssues) / textElements.length) * 100
        };
    }

    /**
     * Check Keyboard Navigation
     */
    checkKeyboardNavigation() {
        const focusableElements = document.querySelectorAll(
            'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
        );

        let keyboardAccessible = 0;

        focusableElements.forEach(element => {
            if (element.tabIndex >= 0) {
                keyboardAccessible++;
            }
        });

        return {
            total: focusableElements.length,
            accessible: keyboardAccessible,
            score: (keyboardAccessible / focusableElements.length) * 100
        };
    }

    /**
     * Generate Validation Report
     */
    generateReport() {
        const report = {
            timestamp: new Date().toISOString(),
            device: this.device,
            results: this.testResults,
            scores: this.calculateScores(),
            recommendations: this.generateRecommendations()
        };

        console.log('📊 Mobile Validation Report:', report);
        return report;
    }

    /**
     * Calculate Overall Scores
     */
    calculateScores() {
        const scores = {
            viewport: this.calculateViewportScore(),
            touchInterface: this.testResults.touchInterface.validationScore || 0,
            performance: this.calculatePerformanceScore(),
            compatibility: this.calculateCompatibilityScore(),
            accessibility: this.calculateAccessibilityScore()
        };

        scores.overall = Object.values(scores).reduce((sum, score) => sum + score, 0) / Object.keys(scores).length;

        return scores;
    }

    /**
     * Calculate Viewport Score
     */
    calculateViewportScore() {
        const viewportResults = this.testResults.viewport;
        let totalScore = 0;
        let count = 0;

        Object.values(viewportResults).forEach(result => {
            if (result.elements) {
                const elementScores = Object.values(result.elements).map(elements => {
                    if (Array.isArray(elements)) {
                        return elements.filter(el => el.accessible || el.touchFriendly).length / elements.length * 100;
                    }
                    return 100;
                });
                totalScore += elementScores.reduce((sum, score) => sum + score, 0) / elementScores.length;
                count++;
            }
        });

        return count > 0 ? totalScore / count : 100;
    }

    /**
     * Calculate Performance Score
     */
    calculatePerformanceScore() {
        const perf = this.testResults.performance;
        let score = 100;

        // Deduct points for poor performance
        if (perf.loading && perf.loading.totalTime > 3000) score -= 20;
        if (perf.fps && perf.fps < 30) score -= 30;
        if (perf.memory && perf.memory.used > 100) score -= 10;

        return Math.max(0, score);
    }

    /**
     * Calculate Compatibility Score
     */
    calculateCompatibilityScore() {
        const compat = this.testResults.compatibility;
        let supportedFeatures = 0;
        let totalFeatures = 0;

        Object.values(compat).forEach(category => {
            if (typeof category === 'object') {
                Object.values(category).forEach(supported => {
                    totalFeatures++;
                    if (supported) supportedFeatures++;
                });
            } else {
                totalFeatures++;
                if (category) supportedFeatures++;
            }
        });

        return (supportedFeatures / totalFeatures) * 100;
    }

    /**
     * Calculate Accessibility Score
     */
    calculateAccessibilityScore() {
        const a11y = this.testResults.accessibility;
        const scores = Object.values(a11y).map(result => result.score || 0);
        return scores.reduce((sum, score) => sum + score, 0) / scores.length;
    }

    /**
     * Generate Recommendations
     */
    generateRecommendations() {
        const recommendations = [];
        const scores = this.calculateScores();

        if (scores.touchInterface < 90) {
            recommendations.push('Increase touch target sizes to minimum 44px');
        }

        if (scores.performance < 80) {
            recommendations.push('Optimize loading performance and reduce memory usage');
        }

        if (scores.accessibility < 90) {
            recommendations.push('Improve accessibility with better ARIA labels and keyboard navigation');
        }

        if (scores.compatibility < 95) {
            recommendations.push('Add polyfills for better browser compatibility');
        }

        return recommendations;
    }

    /**
     * Mobile Navigation Helpers
     */
    openMobileNav() {
        const nav = document.querySelector('.meschain-mobile-nav');
        if (nav) {
            nav.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeMobileNav() {
        const nav = document.querySelector('.meschain-mobile-nav');
        if (nav) {
            nav.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    /**
     * Handle Viewport Changes
     */
    handleViewportChange() {
        console.log('📱 Viewport changed:', window.innerWidth + 'x' + window.innerHeight);
        this.detectDevice();
        this.setupViewportTesting();
    }

    /**
     * Handle Orientation Changes
     */
    handleOrientationChange() {
        console.log('🔄 Orientation changed:', this.getOrientation());
        this.device.orientation = this.getOrientation();
        this.handleViewportChange();
    }
}

// Initialize Mobile Validator when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.mesChainMobileValidator = new MesChainMobileValidator();
    
    // Generate report after 5 seconds
    setTimeout(() => {
        const report = window.mesChainMobileValidator.generateReport();
        console.log('📊 Final Mobile Validation Report Generated');
        
        // Store report for later use
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('meschain_mobile_validation_report', JSON.stringify(report));
        }
    }, 5000);
});

/**
 * Export for use in other modules
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MesChainMobileValidator;
} 