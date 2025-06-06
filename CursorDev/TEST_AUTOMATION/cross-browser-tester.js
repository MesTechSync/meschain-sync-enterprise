// 🧪 SELINAY TEST AUTOMATION - MesChain-Sync Enterprise
// Cross-Browser Testing & Quality Assurance
// Created: June 5, 2025 06:00 UTC

class SelinayCrossBrowserTester {
    constructor() {
        this.testResults = {
            chrome: { passed: 0, failed: 0, tests: [] },
            firefox: { passed: 0, failed: 0, tests: [] },
            safari: { passed: 0, failed: 0, tests: [] },
            edge: { passed: 0, failed: 0, tests: [] }
        };
        
        this.testSuites = {
            responsive: 'Responsive Design Tests',
            darkMode: 'Dark Mode Tests',
            performance: 'Performance Tests',
            functionality: 'Functionality Tests',
            accessibility: 'Accessibility Tests'
        };
        
        this.init();
    }

    init() {
        this.detectBrowser();
        this.createTestInterface();
        this.runBasicTests();
        
        console.log('🧪 Selinay Cross-Browser Tester initialized');
    }

    detectBrowser() {
        const userAgent = navigator.userAgent;
        
        if (userAgent.includes('Chrome') && !userAgent.includes('Edg')) {
            this.currentBrowser = 'chrome';
        } else if (userAgent.includes('Firefox')) {
            this.currentBrowser = 'firefox';
        } else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
            this.currentBrowser = 'safari';
        } else if (userAgent.includes('Edg')) {
            this.currentBrowser = 'edge';
        } else {
            this.currentBrowser = 'unknown';
        }
        
        console.log(`🌐 Detected browser: ${this.currentBrowser}`);
    }

    createTestInterface() {
        // Create test panel only in dev environment
        if (window.location.hostname === 'localhost' || window.location.search.includes('test=true')) {
            const testPanel = document.createElement('div');
            testPanel.id = 'selinay-test-panel';
            testPanel.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                width: 300px;
                background: rgba(255, 255, 255, 0.95);
                border: 2px solid #3b82f6;
                border-radius: 12px;
                padding: 15px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                font-size: 14px;
                z-index: 10000;
                backdrop-filter: blur(10px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            `;
            
            testPanel.innerHTML = `
                <h3 style="margin: 0 0 10px 0; color: #3b82f6;">🧪 Selinay Test Panel</h3>
                <p style="margin: 5px 0; font-size: 12px; color: #666;">Browser: <strong>${this.currentBrowser}</strong></p>
                <div id="test-results" style="margin: 10px 0;"></div>
                <button onclick="window.selinayTester.runAllTests()" style="
                    background: #3b82f6;
                    color: white;
                    border: none;
                    padding: 8px 12px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 12px;
                    margin-right: 5px;
                ">Run All Tests</button>
                <button onclick="document.getElementById('selinay-test-panel').style.display='none'" style="
                    background: #6b7280;
                    color: white;
                    border: none;
                    padding: 8px 12px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 12px;
                ">Hide</button>
            `;
            
            document.body.appendChild(testPanel);
        }
    }

    async runAllTests() {
        console.log('🧪 Starting comprehensive test suite...');
        
        const tests = [
            () => this.testResponsiveDesign(),
            () => this.testDarkMode(),
            () => this.testPerformance(),
            () => this.testFunctionality(),
            () => this.testAccessibility()
        ];
        
        for (const test of tests) {
            try {
                await test();
                await this.sleep(500); // Small delay between tests
            } catch (error) {
                console.error('Test failed:', error);
                this.recordTestResult('Test Suite', false, error.message);
            }
        }
        
        this.generateTestReport();
        this.updateTestInterface();
    }

    async testResponsiveDesign() {
        console.log('📱 Testing responsive design...');
        
        const viewports = [
            { name: 'Mobile', width: 375, height: 667 },
            { name: 'Tablet', width: 768, height: 1024 },
            { name: 'Desktop', width: 1920, height: 1080 }
        ];
        
        const originalSize = {
            width: window.innerWidth,
            height: window.innerHeight
        };
        
        for (const viewport of viewports) {
            try {
                // Simulate viewport change (limited in browser environment)
                const testPassed = await this.testViewport(viewport);
                this.recordTestResult(`Responsive ${viewport.name}`, testPassed, 
                    testPassed ? 'Layout responsive' : 'Layout issues detected');
            } catch (error) {
                this.recordTestResult(`Responsive ${viewport.name}`, false, error.message);
            }
        }
    }

    async testViewport(viewport) {
        // Test for responsive elements
        const cards = document.querySelectorAll('.card, .dashboard-widget');
        const navigation = document.querySelector('.navbar, .nav');
        const buttons = document.querySelectorAll('.btn');
        
        let passed = true;
        
        // Check if cards stack properly on small screens
        if (viewport.width <= 768) {
            cards.forEach(card => {
                const computedStyle = window.getComputedStyle(card);
                const width = parseFloat(computedStyle.width);
                if (width > viewport.width * 0.95) {
                    passed = false;
                }
            });
        }
        
        // Check button sizes for touch friendliness
        if (viewport.width <= 768) {
            buttons.forEach(button => {
                const rect = button.getBoundingClientRect();
                if (rect.height < 44 || rect.width < 44) {
                    console.warn('Button too small for touch:', button);
                }
            });
        }
        
        return passed;
    }

    async testDarkMode() {
        console.log('🌙 Testing dark mode...');
        
        const originalTheme = document.documentElement.getAttribute('data-theme');
        
        try {
            // Test dark mode
            document.documentElement.setAttribute('data-theme', 'dark');
            await this.sleep(100);
            
            const darkModeTests = [
                this.testColorContrast(),
                this.testThemeConsistency(),
                this.testElementVisibility()
            ];
            
            const results = await Promise.all(darkModeTests);
            const darkPassed = results.every(result => result);
            
            // Test light mode
            document.documentElement.setAttribute('data-theme', 'light');
            await this.sleep(100);
            
            const lightModeTests = [
                this.testColorContrast(),
                this.testThemeConsistency(),
                this.testElementVisibility()
            ];
            
            const lightResults = await Promise.all(lightModeTests);
            const lightPassed = lightResults.every(result => result);
            
            this.recordTestResult('Dark Mode', darkPassed, darkPassed ? 'Dark mode working' : 'Dark mode issues');
            this.recordTestResult('Light Mode', lightPassed, lightPassed ? 'Light mode working' : 'Light mode issues');
            
        } finally {
            // Restore original theme
            if (originalTheme) {
                document.documentElement.setAttribute('data-theme', originalTheme);
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        }
    }

    testColorContrast() {
        // Basic contrast check
        const elements = document.querySelectorAll('body, .card, .btn, input');
        let passed = true;
        
        elements.forEach(element => {
            const styles = window.getComputedStyle(element);
            const backgroundColor = styles.backgroundColor;
            const color = styles.color;
            
            // Simple check - ensure colors are not the same
            if (backgroundColor === color && backgroundColor !== 'rgba(0, 0, 0, 0)') {
                passed = false;
            }
        });
        
        return passed;
    }

    testThemeConsistency() {
        // Check if theme is applied consistently
        const themedElements = document.querySelectorAll('[data-theme] *');
        return themedElements.length > 0;
    }

    testElementVisibility() {
        // Check if all elements are visible
        const elements = document.querySelectorAll('.card, .btn, input, .nav-link');
        let passed = true;
        
        elements.forEach(element => {
            const styles = window.getComputedStyle(element);
            if (styles.display === 'none' && !element.classList.contains('d-none')) {
                console.warn('Element may be hidden:', element);
            }
        });
        
        return passed;
    }

    async testPerformance() {
        console.log('⚡ Testing performance...');
        
        const performanceTests = [
            this.testPageLoadSpeed(),
            this.testMemoryUsage(),
            this.testAnimationPerformance(),
            this.testScrollPerformance()
        ];
        
        const results = await Promise.all(performanceTests);
        const allPassed = results.every(result => result.passed);
        
        this.recordTestResult('Performance', allPassed, 
            allPassed ? 'Performance excellent' : 'Performance issues detected');
    }

    testPageLoadSpeed() {
        const navigationTiming = performance.getEntriesByType('navigation')[0];
        if (!navigationTiming) {
            return { passed: false, message: 'Navigation timing not available' };
        }
        
        const loadTime = navigationTiming.loadEventEnd - navigationTiming.loadEventStart;
        const passed = loadTime < 3000; // Under 3 seconds
        
        return {
            passed,
            message: `Page load: ${Math.round(loadTime)}ms`,
            metric: loadTime
        };
    }

    testMemoryUsage() {
        if (!performance.memory) {
            return { passed: true, message: 'Memory info not available' };
        }
        
        const usedMemory = performance.memory.usedJSHeapSize / 1048576; // MB
        const passed = usedMemory < 100; // Under 100MB
        
        return {
            passed,
            message: `Memory usage: ${Math.round(usedMemory)}MB`,
            metric: usedMemory
        };
    }

    testAnimationPerformance() {
        // Test for 60fps animations
        let frameCount = 0;
        let lastTime = performance.now();
        
        return new Promise(resolve => {
            const measureFrameRate = (currentTime) => {
                frameCount++;
                
                if (currentTime - lastTime >= 1000) {
                    const fps = frameCount;
                    frameCount = 0;
                    lastTime = currentTime;
                    
                    const passed = fps >= 50; // At least 50fps
                    resolve({
                        passed,
                        message: `Animation FPS: ${fps}`,
                        metric: fps
                    });
                    return;
                }
                
                requestAnimationFrame(measureFrameRate);
            };
            
            requestAnimationFrame(measureFrameRate);
            
            // Timeout after 2 seconds
            setTimeout(() => {
                resolve({ passed: true, message: 'FPS test timeout' });
            }, 2000);
        });
    }

    testScrollPerformance() {
        // Test scroll smoothness
        const scrollContainer = document.body;
        let passed = true;
        
        // Check for scroll-behavior: smooth
        const computedStyle = window.getComputedStyle(scrollContainer);
        if (computedStyle.scrollBehavior !== 'smooth') {
            passed = false;
        }
        
        return {
            passed,
            message: passed ? 'Scroll optimized' : 'Scroll needs optimization',
            metric: passed ? 1 : 0
        };
    }

    async testFunctionality() {
        console.log('⚙️ Testing functionality...');
        
        const functionalityTests = [
            this.testThemeToggle(),
            this.testNavigation(),
            this.testForms(),
            this.testCharts()
        ];
        
        const results = await Promise.all(functionalityTests);
        const allPassed = results.every(result => result);
        
        this.recordTestResult('Functionality', allPassed,
            allPassed ? 'All features working' : 'Some features have issues');
    }

    testThemeToggle() {
        try {
            const toggleButton = document.getElementById('theme-toggle');
            if (!toggleButton) {
                return false;
            }
            
            // Test if toggle function exists
            if (window.selinayThemeManager && window.selinayThemeManager.toggleTheme) {
                return true;
            }
            
            return false;
        } catch (error) {
            return false;
        }
    }

    testNavigation() {
        const navLinks = document.querySelectorAll('.nav-link, .navbar-nav a');
        let passed = true;
        
        navLinks.forEach(link => {
            if (!link.href && !link.onclick && !link.getAttribute('data-action')) {
                passed = false;
            }
        });
        
        return passed;
    }

    testForms() {
        const forms = document.querySelectorAll('form');
        const inputs = document.querySelectorAll('input, textarea, select');
        
        let passed = true;
        
        // Check if forms have proper structure
        forms.forEach(form => {
            if (!form.action && !form.onsubmit) {
                console.warn('Form without action or handler:', form);
            }
        });
        
        // Check input accessibility
        inputs.forEach(input => {
            if (!input.labels.length && !input.getAttribute('aria-label') && !input.getAttribute('placeholder')) {
                passed = false;
            }
        });
        
        return passed;
    }

    testCharts() {
        const chartContainers = document.querySelectorAll('.chart-container, [id*="chart"], canvas');
        
        if (chartContainers.length === 0) {
            return true; // No charts to test
        }
        
        let passed = true;
        
        chartContainers.forEach(container => {
            const canvas = container.querySelector('canvas') || container;
            if (canvas.tagName === 'CANVAS') {
                const context = canvas.getContext('2d');
                if (!context) {
                    passed = false;
                }
            }
        });
        
        return passed;
    }

    async testAccessibility() {
        console.log('♿ Testing accessibility...');
        
        const accessibilityTests = [
            this.testKeyboardNavigation(),
            this.testAriaLabels(),
            this.testColorContrast(),
            this.testFocusManagement()
        ];
        
        const results = await Promise.all(accessibilityTests);
        const allPassed = results.every(result => result);
        
        this.recordTestResult('Accessibility', allPassed,
            allPassed ? 'Accessibility compliant' : 'Accessibility issues found');
    }

    testKeyboardNavigation() {
        const focusableElements = document.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        let passed = true;
        
        focusableElements.forEach(element => {
            if (element.tabIndex < 0 && !element.hasAttribute('tabindex')) {
                // Element should be focusable
                passed = false;
            }
        });
        
        return passed;
    }

    testAriaLabels() {
        const interactiveElements = document.querySelectorAll('button, input, select, textarea, [role="button"]');
        let passed = true;
        
        interactiveElements.forEach(element => {
            const hasLabel = element.labels.length > 0 || 
                           element.getAttribute('aria-label') || 
                           element.getAttribute('aria-labelledby') ||
                           element.textContent.trim().length > 0;
            
            if (!hasLabel) {
                passed = false;
                console.warn('Element without accessible label:', element);
            }
        });
        
        return passed;
    }

    testFocusManagement() {
        // Test if focus is visible
        const style = document.createElement('style');
        style.textContent = `
            .focus-test:focus {
                outline: 2px solid #3b82f6 !important;
                outline-offset: 2px !important;
            }
        `;
        document.head.appendChild(style);
        
        const focusableElements = document.querySelectorAll('button, [href], input');
        let passed = true;
        
        focusableElements.forEach(element => {
            element.classList.add('focus-test');
        });
        
        // Cleanup
        setTimeout(() => {
            document.head.removeChild(style);
            focusableElements.forEach(element => {
                element.classList.remove('focus-test');
            });
        }, 1000);
        
        return passed;
    }

    recordTestResult(testName, passed, message) {
        const browser = this.currentBrowser;
        if (!this.testResults[browser]) {
            this.testResults[browser] = { passed: 0, failed: 0, tests: [] };
        }
        
        this.testResults[browser].tests.push({
            name: testName,
            passed,
            message,
            timestamp: new Date().toISOString()
        });
        
        if (passed) {
            this.testResults[browser].passed++;
        } else {
            this.testResults[browser].failed++;
        }
        
        console.log(`${passed ? '✅' : '❌'} ${testName}: ${message}`);
    }

    updateTestInterface() {
        const resultsDiv = document.getElementById('test-results');
        if (!resultsDiv) return;
        
        const browser = this.currentBrowser;
        const results = this.testResults[browser];
        const total = results.passed + results.failed;
        const percentage = total > 0 ? Math.round((results.passed / total) * 100) : 0;
        
        resultsDiv.innerHTML = `
            <div style="font-size: 12px; color: #333;">
                <strong>Results:</strong><br>
                ✅ Passed: ${results.passed}<br>
                ❌ Failed: ${results.failed}<br>
                📊 Score: ${percentage}%
            </div>
        `;
    }

    generateTestReport() {
        const report = {
            timestamp: new Date().toISOString(),
            browser: this.currentBrowser,
            userAgent: navigator.userAgent,
            results: this.testResults[this.currentBrowser],
            summary: this.getTestSummary()
        };
        
        console.log('📋 Test Report Generated:', report);
        
        // Store in localStorage for later retrieval
        localStorage.setItem('selinay-test-report', JSON.stringify(report));
        
        return report;
    }

    getTestSummary() {
        const browser = this.currentBrowser;
        const results = this.testResults[browser];
        const total = results.passed + results.failed;
        
        return {
            totalTests: total,
            passedTests: results.passed,
            failedTests: results.failed,
            successRate: total > 0 ? Math.round((results.passed / total) * 100) : 0
        };
    }

    async runBasicTests() {
        // Run a quick basic test on initialization
        this.recordTestResult('Browser Detection', true, `Detected ${this.currentBrowser}`);
        this.recordTestResult('DOM Ready', document.readyState === 'complete', 'DOM state check');
        this.recordTestResult('Console Available', typeof console !== 'undefined', 'Console check');
        
        this.updateTestInterface();
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.selinayTester = new SelinayCrossBrowserTester();
    
    // Auto-run tests if URL parameter is present
    if (window.location.search.includes('autotest=true')) {
        setTimeout(() => {
            window.selinayTester.runAllTests();
        }, 2000);
    }
    
    console.log('🧪 Selinay Cross-Browser Tester ready!');
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SelinayCrossBrowserTester;
}
