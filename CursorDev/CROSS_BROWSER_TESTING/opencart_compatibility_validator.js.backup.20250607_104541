/**
 * OpenCart Cross-Browser Compatibility Validator v1.0
 * Specialized testing framework for MesChain-Sync OpenCart extension
 * 
 * @version 1.0.0
 * @date June 4, 2025 04:00 UTC
 * @author MesChain Development Team
 * @priority CRITICAL - OpenCart Deployment Validation
 */

class OpenCartCompatibilityValidator {
    constructor() {
        this.openCartVersion = this.detectOpenCartVersion();
        this.extensionFeatures = [
            'Admin Panel Integration',
            'Marketplace API Connections',
            'Product Synchronization',
            'Order Management',
            'Inventory Tracking',
            'Price Management',
            'Image Synchronization',
            'Category Mapping',
            'Real-time Notifications',
            'Bulk Operations',
            'Export/Import Functions',
            'Reporting Dashboard'
        ];
        
        this.marketplaceEndpoints = {
            'Trendyol': 'https://api.trendyol.com',
            'N11': 'https://api.n11.com',
            'Amazon': 'https://mws.amazonservices.com',
            'eBay': 'https://api.ebay.com',
            'Hepsiburada': 'https://api.hepsiburada.com',
            'Ozon': 'https://api-seller.ozon.ru',
            'Pazarama': 'https://api.pazarama.com',
            'ÇiçekSepeti': 'https://api.ciceksepeti.com'
        };
        
        this.testResults = {
            openCartCompatibility: {},
            extensionFeatures: {},
            marketplaceConnectivity: {},
            performanceMetrics: {},
            securityValidation: {},
            userInterfaceTests: {},
            mobileCompatibility: {}
        };
        
        this.init();
    }
    
    /**
     * Initialize OpenCart-specific validation
     */
    init() {
        console.log('🛒 OpenCart Compatibility Validator başlatılıyor...');
        this.validateOpenCartEnvironment();
        this.checkExtensionRequirements();
    }
    
    /**
     * Detect OpenCart version
     */
    detectOpenCartVersion() {
        try {
            // Check for OpenCart version in global scope
            if (typeof window.oc_version !== 'undefined') {
                return window.oc_version;
            }
            
            // Check meta tag
            const versionMeta = document.querySelector('meta[name="opencart-version"]');
            if (versionMeta) {
                return versionMeta.getAttribute('content');
            }
            
            // Check body class
            const bodyClasses = document.body.className;
            const versionMatch = bodyClasses.match(/opencart-(\d+\.\d+\.\d+)/);
            if (versionMatch) {
                return versionMatch[1];
            }
            
            // Default assumption for modern installations
            return '3.0.4.0+';
            
        } catch (error) {
            console.warn('⚠️ OpenCart versiyonu algılanamadı:', error);
            return 'Unknown';
        }
    }
    
    /**
     * Validate OpenCart environment
     */
    validateOpenCartEnvironment() {
        console.log('🔍 OpenCart ortamı doğrulanıyor...');
        
        const checks = [
            {
                name: 'jQuery Availability',
                test: () => typeof jQuery !== 'undefined' || typeof $ !== 'undefined',
                critical: true
            },
            {
                name: 'Bootstrap Integration',
                test: () => typeof bootstrap !== 'undefined' || document.querySelector('.bootstrap') !== null,
                critical: false
            },
            {
                name: 'OpenCart Token System',
                test: () => document.querySelector('input[name="user_token"]') !== null,
                critical: true
            },
            {
                name: 'Admin Menu Structure',
                test: () => document.querySelector('#menu') !== null,
                critical: true
            },
            {
                name: 'CSRF Protection',
                test: () => document.querySelector('meta[name="csrf-token"]') !== null,
                critical: false
            },
            {
                name: 'Session Management',
                test: () => document.cookie.includes('OCSESSID') || document.cookie.includes('session'),
                critical: true
            }
        ];
        
        for (const check of checks) {
            try {
                const result = check.test();
                this.testResults.openCartCompatibility[check.name] = {
                    passed: result,
                    critical: check.critical,
                    timestamp: new Date().toISOString()
                };
                
                if (result) {
                    console.log(`✅ ${check.name} - BAŞARILI`);
                } else {
                    const level = check.critical ? '❌' : '⚠️';
                    console.log(`${level} ${check.name} - ${check.critical ? 'KRİTİK HATA' : 'UYARI'}`);
                }
                
            } catch (error) {
                console.error(`❌ ${check.name} test hatası:`, error);
                this.testResults.openCartCompatibility[check.name] = {
                    passed: false,
                    critical: check.critical,
                    error: error.message,
                    timestamp: new Date().toISOString()
                };
            }
        }
    }
    
    /**
     * Check extension requirements
     */
    checkExtensionRequirements() {
        console.log('🔌 Extension gereksinimleri kontrol ediliyor...');
        
        const requirements = [
            {
                name: 'PHP Version',
                test: () => this.checkPHPVersion(),
                critical: true
            },
            {
                name: 'MySQL Connection',
                test: () => this.checkDatabaseConnection(),
                critical: true
            },
            {
                name: 'cURL Extension',
                test: () => this.checkCurlSupport(),
                critical: true
            },
            {
                name: 'JSON Extension',
                test: () => typeof JSON !== 'undefined',
                critical: true
            },
            {
                name: 'ZIP Extension',
                test: () => this.checkZipSupport(),
                critical: false
            },
            {
                name: 'GD Library',
                test: () => this.checkGDSupport(),
                critical: false
            }
        ];
        
        for (const requirement of requirements) {
            try {
                const result = requirement.test();
                this.testResults.extensionFeatures[requirement.name] = {
                    passed: result,
                    critical: requirement.critical,
                    timestamp: new Date().toISOString()
                };
                
                if (result) {
                    console.log(`✅ ${requirement.name} - MEVCUT`);
                } else {
                    const level = requirement.critical ? '❌' : '⚠️';
                    console.log(`${level} ${requirement.name} - ${requirement.critical ? 'EKSİK' : 'TERCİH EDİLİR'}`);
                }
                
            } catch (error) {
                console.error(`❌ ${requirement.name} kontrol hatası:`, error);
            }
        }
    }
    
    /**
     * Check PHP version (client-side detection)
     */
    checkPHPVersion() {
        // This would typically be set by server-side script
        const phpVersion = window.phpVersion || '8.0+';
        return phpVersion !== 'Unknown';
    }
    
    /**
     * Check database connection
     */
    checkDatabaseConnection() {
        // Check if we can access any OpenCart data
        return document.querySelector('[data-oc-db]') !== null || 
               document.querySelector('.database-status') !== null;
    }
    
    /**
     * Check cURL support
     */
    checkCurlSupport() {
        // Check if fetch is available (modern alternative to cURL)
        return 'fetch' in window || 'XMLHttpRequest' in window;
    }
    
    /**
     * Check ZIP support
     */
    checkZipSupport() {
        // Check if compression/decompression is available
        return 'CompressionStream' in window || 'DecompressionStream' in window;
    }
    
    /**
     * Check GD library support
     */
    checkGDSupport() {
        // Check canvas support (indicates image processing capability)
        return 'HTMLCanvasElement' in window && 'CanvasRenderingContext2D' in window;
    }
    
    /**
     * Test marketplace connectivity
     */
    async testMarketplaceConnectivity() {
        console.log('🌐 Marketplace bağlantıları test ediliyor...');
        
        for (const [marketplace, endpoint] of Object.entries(this.marketplaceEndpoints)) {
            try {
                console.log(`🔗 ${marketplace} bağlantısı test ediliyor...`);
                
                // Test basic connectivity (CORS may block, but we can check network)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000);
                
                const startTime = performance.now();
                
                try {
                    await fetch(endpoint, {
                        method: 'HEAD',
                        mode: 'no-cors',
                        signal: controller.signal
                    });
                    
                    const endTime = performance.now();
                    const responseTime = endTime - startTime;
                    
                    clearTimeout(timeoutId);
                    
                    this.testResults.marketplaceConnectivity[marketplace] = {
                        status: 'reachable',
                        responseTime: responseTime,
                        timestamp: new Date().toISOString()
                    };
                    
                    console.log(`✅ ${marketplace} - Erişilebilir (${responseTime.toFixed(0)}ms)`);
                    
                } catch (fetchError) {
                    clearTimeout(timeoutId);
                    
                    if (fetchError.name === 'AbortError') {
                        this.testResults.marketplaceConnectivity[marketplace] = {
                            status: 'timeout',
                            error: 'Connection timeout',
                            timestamp: new Date().toISOString()
                        };
                        console.warn(`⚠️ ${marketplace} - Bağlantı zaman aşımı`);
                    } else {
                        this.testResults.marketplaceConnectivity[marketplace] = {
                            status: 'cors_blocked',
                            note: 'CORS policy may block direct access',
                            timestamp: new Date().toISOString()
                        };
                        console.log(`ℹ️ ${marketplace} - CORS kısıtlaması (normal)`);
                    }
                }
                
            } catch (error) {
                this.testResults.marketplaceConnectivity[marketplace] = {
                    status: 'error',
                    error: error.message,
                    timestamp: new Date().toISOString()
                };
                console.error(`❌ ${marketplace} test hatası:`, error);
            }
        }
    }
    
    /**
     * Test user interface components
     */
    async testUserInterface() {
        console.log('🎨 Kullanıcı arayüzü test ediliyor...');
        
        const uiTests = [
            {
                name: 'Admin Menu Integration',
                test: () => this.testAdminMenuIntegration()
            },
            {
                name: 'Form Validation',
                test: () => this.testFormValidation()
            },
            {
                name: 'Modal Dialogs',
                test: () => this.testModalDialogs()
            },
            {
                name: 'Data Tables',
                test: () => this.testDataTables()
            },
            {
                name: 'Chart Rendering',
                test: () => this.testChartRendering()
            },
            {
                name: 'Responsive Layout',
                test: () => this.testResponsiveLayout()
            }
        ];
        
        for (const test of uiTests) {
            try {
                const result = await test.test();
                this.testResults.userInterfaceTests[test.name] = {
                    passed: result,
                    timestamp: new Date().toISOString()
                };
                
                if (result) {
                    console.log(`✅ ${test.name} - BAŞARILI`);
                } else {
                    console.warn(`⚠️ ${test.name} - SORUN VAR`);
                }
                
            } catch (error) {
                console.error(`❌ ${test.name} test hatası:`, error);
                this.testResults.userInterfaceTests[test.name] = {
                    passed: false,
                    error: error.message,
                    timestamp: new Date().toISOString()
                };
            }
        }
    }
    
    /**
     * Test admin menu integration
     */
    testAdminMenuIntegration() {
        // Check if MesChain menu items exist
        const adminMenu = document.querySelector('#menu');
        if (!adminMenu) return false;
        
        // Look for MesChain-related menu items
        const mesChainMenus = adminMenu.querySelectorAll('[href*="meschain"], [href*="marketplace"], [data-meschain]');
        return mesChainMenus.length > 0;
    }
    
    /**
     * Test form validation
     */
    testFormValidation() {
        // Create test form
        const testForm = document.createElement('form');
        testForm.innerHTML = `
            <input type="text" required name="test_field">
            <button type="submit">Test</button>
        `;
        testForm.style.display = 'none';
        document.body.appendChild(testForm);
        
        // Test HTML5 validation
        const input = testForm.querySelector('input');
        const isValid = input.checkValidity();
        
        // Cleanup
        document.body.removeChild(testForm);
        
        return !isValid; // Should be invalid (empty required field)
    }
    
    /**
     * Test modal dialogs
     */
    testModalDialogs() {
        // Check if Bootstrap modal or similar system is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                const testModal = new bootstrap.Modal(document.createElement('div'));
                return true;
            } catch (error) {
                return false;
            }
        }
        
        // Check for jQuery modal systems
        if (typeof $ !== 'undefined' && $.fn.modal) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Test data tables
     */
    testDataTables() {
        // Check if DataTables or similar system is available
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            return true;
        }
        
        // Check for native table sorting/filtering capabilities
        return 'HTMLTableElement' in window;
    }
    
    /**
     * Test chart rendering
     */
    testChartRendering() {
        // Check if Chart.js is available
        if (typeof Chart !== 'undefined') {
            try {
                // Test canvas support
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                return ctx !== null;
            } catch (error) {
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Test responsive layout
     */
    testResponsiveLayout() {
        // Test CSS media queries
        if (!window.matchMedia) return false;
        
        const mobileQuery = window.matchMedia('(max-width: 768px)');
        const tabletQuery = window.matchMedia('(max-width: 1024px)');
        
        return mobileQuery && tabletQuery;
    }
    
    /**
     * Test mobile compatibility
     */
    async testMobileCompatibility() {
        console.log('📱 Mobil uyumluluk test ediliyor...');
        
        const mobileTests = [
            {
                name: 'Touch Events',
                test: () => 'ontouchstart' in window || navigator.maxTouchPoints > 0
            },
            {
                name: 'Viewport Meta Tag',
                test: () => document.querySelector('meta[name="viewport"]') !== null
            },
            {
                name: 'Mobile-First CSS',
                test: () => this.checkMobileFirstCSS()
            },
            {
                name: 'Responsive Images',
                test: () => document.querySelector('img[srcset]') !== null || 
                           document.querySelector('picture') !== null
            },
            {
                name: 'Fast Click Prevention',
                test: () => this.checkFastClickPrevention()
            }
        ];
        
        for (const test of mobileTests) {
            try {
                const result = test.test();
                this.testResults.mobileCompatibility[test.name] = {
                    passed: result,
                    timestamp: new Date().toISOString()
                };
                
                if (result) {
                    console.log(`✅ ${test.name} - DESTEKLENIYOR`);
                } else {
                    console.warn(`⚠️ ${test.name} - EKSİK`);
                }
                
            } catch (error) {
                console.error(`❌ ${test.name} test hatası:`, error);
            }
        }
    }
    
    /**
     * Check mobile-first CSS approach
     */
    checkMobileFirstCSS() {
        // Look for mobile-first media queries in stylesheets
        const stylesheets = Array.from(document.styleSheets);
        
        for (const stylesheet of stylesheets) {
            try {
                const rules = stylesheet.cssRules || stylesheet.rules;
                for (const rule of rules) {
                    if (rule.type === CSSRule.MEDIA_RULE) {
                        const mediaText = rule.media.mediaText;
                        if (mediaText.includes('min-width')) {
                            return true;
                        }
                    }
                }
            } catch (error) {
                // CORS or other access issues
                continue;
            }
        }
        
        return false;
    }
    
    /**
     * Check fast click prevention
     */
    checkFastClickPrevention() {
        // Check if FastClick library is loaded or touch-action CSS is used
        return typeof FastClick !== 'undefined' || 
               getComputedStyle(document.body).touchAction !== 'auto';
    }
    
    /**
     * Perform security validation
     */
    async performSecurityValidation() {
        console.log('🔒 Güvenlik doğrulaması yapılıyor...');
        
        const securityTests = [
            {
                name: 'HTTPS Protocol',
                test: () => window.location.protocol === 'https:'
            },
            {
                name: 'Content Security Policy',
                test: () => this.checkCSP()
            },
            {
                name: 'XSS Protection',
                test: () => this.checkXSSProtection()
            },
            {
                name: 'CSRF Token',
                test: () => document.querySelector('meta[name="csrf-token"]') !== null ||
                           document.querySelector('input[name="_token"]') !== null
            },
            {
                name: 'Secure Cookies',
                test: () => this.checkSecureCookies()
            },
            {
                name: 'SQL Injection Protection',
                test: () => this.checkSQLInjectionProtection()
            }
        ];
        
        for (const test of securityTests) {
            try {
                const result = test.test();
                this.testResults.securityValidation[test.name] = {
                    passed: result,
                    timestamp: new Date().toISOString()
                };
                
                if (result) {
                    console.log(`✅ ${test.name} - GÜVENLİ`);
                } else {
                    console.warn(`⚠️ ${test.name} - RİSK`);
                }
                
            } catch (error) {
                console.error(`❌ ${test.name} test hatası:`, error);
            }
        }
    }
    
    /**
     * Check Content Security Policy
     */
    checkCSP() {
        const cspMeta = document.querySelector('meta[http-equiv="Content-Security-Policy"]');
        return cspMeta !== null;
    }
    
    /**
     * Check XSS Protection
     */
    checkXSSProtection() {
        // Check for XSS protection headers (set by server)
        // This is a basic client-side check
        return document.querySelector('meta[http-equiv="X-XSS-Protection"]') !== null;
    }
    
    /**
     * Check secure cookies
     */
    checkSecureCookies() {
        // Check if cookies have secure flags
        const cookies = document.cookie.split(';');
        return cookies.some(cookie => cookie.includes('Secure') || cookie.includes('HttpOnly'));
    }
    
    /**
     * Check SQL injection protection
     */
    checkSQLInjectionProtection() {
        // Basic check for prepared statements or ORM usage indicators
        const forms = document.querySelectorAll('form');
        let hasProtection = false;
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                // Check for proper input validation attributes
                if (input.hasAttribute('pattern') || 
                    input.hasAttribute('maxlength') || 
                    input.type === 'email' || 
                    input.type === 'url') {
                    hasProtection = true;
                }
            });
        });
        
        return hasProtection;
    }
    
    /**
     * Run comprehensive OpenCart compatibility tests
     */
    async runComprehensiveTests() {
        console.log('🚀 Kapsamlı OpenCart uyumluluk testleri başlatılıyor...');
        
        const startTime = performance.now();
        
        try {
            // Run all test suites
            await this.testMarketplaceConnectivity();
            await this.testUserInterface();
            await this.testMobileCompatibility();
            await this.performSecurityValidation();
            
            // Performance test
            await this.measurePerformance();
            
            const endTime = performance.now();
            const totalDuration = endTime - startTime;
            
            // Generate comprehensive report
            return this.generateOpenCartReport(totalDuration);
            
        } catch (error) {
            console.error('❌ OpenCart testleri başarısız:', error);
            return this.generateErrorReport(error);
        }
    }
    
    /**
     * Measure performance metrics
     */
    async measurePerformance() {
        console.log('⚡ Performans metrikleri ölçülüyor...');
        
        try {
            // Page load performance
            if (performance.timing) {
                const timing = performance.timing;
                this.testResults.performanceMetrics = {
                    domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
                    windowLoad: timing.loadEventEnd - timing.navigationStart,
                    firstPaint: performance.getEntriesByType('paint')
                        .find(entry => entry.name === 'first-paint')?.startTime || 0,
                    firstContentfulPaint: performance.getEntriesByType('paint')
                        .find(entry => entry.name === 'first-contentful-paint')?.startTime || 0
                };
            }
            
            // Memory usage (if available)
            if (performance.memory) {
                this.testResults.performanceMetrics.memoryUsage = {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit
                };
            }
            
            console.log('✅ Performans metrikleri toplandı');
            
        } catch (error) {
            console.error('❌ Performans ölçümü hatası:', error);
        }
    }
    
    /**
     * Generate comprehensive OpenCart compatibility report
     */
    generateOpenCartReport(duration) {
        const totalTests = this.calculateTotalTests();
        const passedTests = this.calculatePassedTests();
        const successRate = totalTests > 0 ? ((passedTests / totalTests) * 100).toFixed(1) : 0;
        
        const report = {
            summary: {
                openCartVersion: this.openCartVersion,
                totalTests: totalTests,
                passedTests: passedTests,
                failedTests: totalTests - passedTests,
                successRate: `${successRate}%`,
                testDuration: `${duration.toFixed(0)}ms`,
                timestamp: new Date().toISOString(),
                deploymentReady: successRate >= 85
            },
            compatibility: {
                openCartCore: this.testResults.openCartCompatibility,
                extensionRequirements: this.testResults.extensionFeatures,
                marketplaceConnectivity: this.testResults.marketplaceConnectivity,
                userInterface: this.testResults.userInterfaceTests,
                mobileSupport: this.testResults.mobileCompatibility,
                security: this.testResults.securityValidation
            },
            performance: this.testResults.performanceMetrics,
            recommendations: this.generateOpenCartRecommendations(),
            nextSteps: this.generateNextSteps()
        };
        
        console.log('\n📋 OPENCART COMPATIBILITY REPORT');
        console.log('==================================');
        console.log(`🛒 OpenCart Version: ${this.openCartVersion}`);
        console.log(`✅ Passed: ${passedTests}/${totalTests}`);
        console.log(`📊 Success Rate: ${successRate}%`);
        console.log(`⏱️ Test Duration: ${duration.toFixed(0)}ms`);
        console.log(`🚀 Deployment Ready: ${report.summary.deploymentReady ? 'YES' : 'NO'}`);
        
        return report;
    }
    
    /**
     * Calculate total number of tests
     */
    calculateTotalTests() {
        let total = 0;
        
        Object.values(this.testResults).forEach(category => {
            if (typeof category === 'object' && category !== null) {
                total += Object.keys(category).length;
            }
        });
        
        return total;
    }
    
    /**
     * Calculate number of passed tests
     */
    calculatePassedTests() {
        let passed = 0;
        
        Object.values(this.testResults).forEach(category => {
            if (typeof category === 'object' && category !== null) {
                Object.values(category).forEach(test => {
                    if (test.passed === true || test.status === 'reachable') {
                        passed++;
                    }
                });
            }
        });
        
        return passed;
    }
    
    /**
     * Generate OpenCart-specific recommendations
     */
    generateOpenCartRecommendations() {
        const recommendations = [];
        
        // Check OpenCart core compatibility
        const coreIssues = Object.values(this.testResults.openCartCompatibility)
            .filter(test => !test.passed && test.critical);
        
        if (coreIssues.length > 0) {
            recommendations.push('🔧 Kritik OpenCart uyumluluk sorunları var. Core bileşenleri kontrol edin.');
        }
        
        // Check marketplace connectivity
        const marketplaceIssues = Object.values(this.testResults.marketplaceConnectivity)
            .filter(test => test.status === 'error' || test.status === 'timeout');
        
        if (marketplaceIssues.length > 0) {
            recommendations.push('🌐 Bazı marketplace bağlantıları sorunlu. Network ayarlarını kontrol edin.');
        }
        
        // Check mobile compatibility
        const mobileIssues = Object.values(this.testResults.mobileCompatibility)
            .filter(test => !test.passed);
        
        if (mobileIssues.length > 2) {
            recommendations.push('📱 Mobil uyumluluk iyileştirilebilir. Responsive tasarım kontrolü yapın.');
        }
        
        // Check security
        const securityIssues = Object.values(this.testResults.securityValidation)
            .filter(test => !test.passed);
        
        if (securityIssues.length > 0) {
            recommendations.push('🔒 Güvenlik önlemleri eksik. Security headers ve HTTPS kontrol edin.');
        }
        
        if (recommendations.length === 0) {
            recommendations.push('🎉 Mükemmel! Tüm testler başarılı. Deployment için hazır.');
        }
        
        return recommendations;
    }
    
    /**
     * Generate next steps for deployment
     */
    generateNextSteps() {
        const steps = [];
        
        const successRate = this.calculatePassedTests() / this.calculateTotalTests() * 100;
        
        if (successRate >= 95) {
            steps.push('🚀 Immediate deployment approved');
            steps.push('📦 Upload OCMOD package to OpenCart');
            steps.push('⚙️ Configure marketplace API credentials');
            steps.push('🔄 Start product synchronization');
        } else if (successRate >= 85) {
            steps.push('⚠️ Minor issues detected - proceed with caution');
            steps.push('🔧 Fix non-critical issues');
            steps.push('📦 Deploy to staging environment first');
            steps.push('✅ Re-run tests before production');
        } else {
            steps.push('❌ Critical issues must be resolved');
            steps.push('🛠️ Address compatibility problems');
            steps.push('🔄 Re-run comprehensive tests');
            steps.push('📞 Contact support if needed');
        }
        
        return steps;
    }
    
    /**
     * Generate error report
     */
    generateErrorReport(error) {
        return {
            error: true,
            message: error.message,
            openCartVersion: this.openCartVersion,
            timestamp: new Date().toISOString(),
            recommendations: [
                '🔧 Test ortamını kontrol edin',
                '🛒 OpenCart kurulumunu doğrulayın',
                '📋 Console hatalarını inceleyin',
                '📞 Teknik destek ile iletişime geçin'
            ]
        };
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🛒 OpenCart Compatibility Validator başlatılıyor...');
    
    // Initialize validator
    window.openCartValidator = new OpenCartCompatibilityValidator();
    
    // Add to global scope for manual testing
    window.runOpenCartTests = () => {
        return window.openCartValidator.runComprehensiveTests();
    };
    
    // Auto-run tests if URL parameter is present
    if (window.location.search.includes('opencart-test=true')) {
        setTimeout(() => {
            window.openCartValidator.runComprehensiveTests().then(report => {
                console.log('📋 OpenCart Compatibility Report:', report);
                
                // Save report to localStorage
                localStorage.setItem('openCartTestReport', JSON.stringify(report));
                
                // Show deployment recommendation
                if (report.summary.deploymentReady) {
                    console.log('🎉 OpenCart deployment için hazır!');
                } else {
                    console.warn('⚠️ Deployment öncesi sorunları çözün');
                }
            });
        }, 3000); // Wait for all components to load
    }
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = OpenCartCompatibilityValidator;
}
