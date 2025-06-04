/**
 * Cross-Browser Testing Configuration v1.0
 * Browser compatibility matrix and testing scenarios for MesChain-Sync
 * 
 * @version 1.0.0
 * @date June 4, 2025 04:00 UTC
 * @author MesChain Development Team
 */

const CrossBrowserTestConfig = {
    // Supported browsers and minimum versions
    supportedBrowsers: {
        chrome: {
            name: 'Google Chrome',
            minVersion: 125,
            engine: 'Blink',
            marketShare: 65.4,
            testPriority: 'high',
            features: {
                es6: true,
                cssGrid: true,
                serviceWorkers: true,
                webGL: true,
                webAssembly: true
            }
        },
        firefox: {
            name: 'Mozilla Firefox',
            minVersion: 115,
            engine: 'Gecko',
            marketShare: 8.9,
            testPriority: 'high',
            features: {
                es6: true,
                cssGrid: true,
                serviceWorkers: true,
                webGL: true,
                webAssembly: true
            }
        },
        safari: {
            name: 'Safari',
            minVersion: 16,
            engine: 'WebKit',
            marketShare: 12.1,
            testPriority: 'high',
            features: {
                es6: true,
                cssGrid: true,
                serviceWorkers: true,
                webGL: true,
                webAssembly: true
            }
        },
        edge: {
            name: 'Microsoft Edge',
            minVersion: 125,
            engine: 'Blink',
            marketShare: 8.3,
            testPriority: 'medium',
            features: {
                es6: true,
                cssGrid: true,
                serviceWorkers: true,
                webGL: true,
                webAssembly: true
            }
        },
        opera: {
            name: 'Opera',
            minVersion: 110,
            engine: 'Blink',
            marketShare: 1.8,
            testPriority: 'low',
            features: {
                es6: true,
                cssGrid: true,
                serviceWorkers: true,
                webGL: true,
                webAssembly: true
            }
        }
    },

    // Critical features that must work across all browsers
    criticalFeatures: {
        // Core Web Technologies
        html5: {
            name: 'HTML5 Features',
            tests: [
                'semantic-elements',
                'form-validation',
                'local-storage',
                'session-storage',
                'canvas-api'
            ],
            fallbacks: ['html4-compatibility']
        },
        css3: {
            name: 'CSS3 Features',
            tests: [
                'flexbox',
                'grid',
                'transforms',
                'transitions',
                'animations',
                'custom-properties',
                'media-queries'
            ],
            fallbacks: ['css2-compatibility', 'vendor-prefixes']
        },
        javascript: {
            name: 'JavaScript ES6+',
            tests: [
                'arrow-functions',
                'template-literals',
                'destructuring',
                'async-await',
                'modules',
                'classes',
                'promises'
            ],
            fallbacks: ['babel-polyfills', 'es5-compatibility']
        },

        // Framework Dependencies
        bootstrap: {
            name: 'Bootstrap 5',
            tests: [
                'grid-system',
                'components',
                'utilities',
                'javascript-plugins'
            ],
            fallbacks: ['bootstrap-4-compatibility']
        },
        chartjs: {
            name: 'Chart.js',
            tests: [
                'chart-creation',
                'chart-types',
                'responsive-charts',
                'chart-animations'
            ],
            fallbacks: ['static-charts', 'table-fallback']
        },
        fontawesome: {
            name: 'Font Awesome 6',
            tests: [
                'icon-rendering',
                'svg-support',
                'css-classes'
            ],
            fallbacks: ['font-awesome-5', 'unicode-icons']
        },

        // PWA Features
        pwa: {
            name: 'Progressive Web App',
            tests: [
                'service-workers',
                'web-manifest',
                'offline-support',
                'push-notifications'
            ],
            fallbacks: ['traditional-web-app']
        },

        // API Features
        apis: {
            name: 'Modern Web APIs',
            tests: [
                'fetch-api',
                'websockets',
                'geolocation',
                'notifications',
                'file-api'
            ],
            fallbacks: ['xhr-requests', 'polling']
        }
    },

    // Testing scenarios for marketplace integrations
    testScenarios: {
        amazon: {
            name: 'Amazon Integration',
            url: '/CursorDev/MARKETPLACE_UIS/amazon_integration.html',
            criticalElements: [
                '.amazon-dashboard',
                '.amazon-metrics',
                '.amazon-chart',
                '.amazon-controls'
            ],
            interactions: [
                'data-refresh',
                'chart-toggle',
                'theme-switch',
                'responsive-layout'
            ]
        },
        trendyol: {
            name: 'Trendyol Integration',
            url: '/CursorDev/MARKETPLACE_UIS/trendyol_integration.html',
            criticalElements: [
                '.trendyol-dashboard',
                '.trendyol-metrics',
                '.trendyol-chart',
                '.trendyol-controls'
            ],
            interactions: [
                'data-refresh',
                'chart-toggle',
                'theme-switch',
                'responsive-layout'
            ]
        },
        hepsiburada: {
            name: 'Hepsiburada Integration',
            url: '/CursorDev/MARKETPLACE_UIS/hepsiburada_integration.html',
            criticalElements: [
                '.hepsiburada-dashboard',
                '.hepsiburada-metrics',
                '.hepsiburada-chart',
                '.hepsiburada-controls'
            ],
            interactions: [
                'data-refresh',
                'chart-toggle',
                'theme-switch',
                'responsive-layout'
            ]
        },
        ebay: {
            name: 'eBay Integration',
            url: '/CursorDev/MARKETPLACE_UIS/ebay_integration.html',
            criticalElements: [
                '.ebay-dashboard',
                '.ebay-metrics',
                '.ebay-chart',
                '.ebay-controls'
            ],
            interactions: [
                'data-refresh',
                'chart-toggle',
                'theme-switch',
                'responsive-layout'
            ]
        },
        n11: {
            name: 'N11 Integration',
            url: '/CursorDev/MARKETPLACE_UIS/n11_integration.html',
            criticalElements: [
                '.n11-dashboard',
                '.n11-metrics',
                '.n11-chart',
                '.n11-controls'
            ],
            interactions: [
                'data-refresh',
                'chart-toggle',
                'theme-switch',
                'responsive-layout'
            ]
        },
        ciceksepeti: {
            name: 'ÇiçekSepeti Integration',
            url: '/CursorDev/MARKETPLACE_UIS/ciceksepeti_integration.html',
            criticalElements: [
                '.ciceksepeti-dashboard',
                '.ciceksepeti-metrics',
                '.ciceksepeti-chart',
                '.ciceksepeti-controls'
            ],
            interactions: [
                'data-refresh',
                'chart-toggle',
                'theme-switch',
                'responsive-layout'
            ]
        },
        ozon: {
            name: 'Ozon Integration',
            url: '/CursorDev/MARKETPLACE_UIS/ozon_integration.html',
            criticalElements: [
                '.ozon-dashboard',
                '.ozon-metrics',
                '.ozon-chart',
                '.ozon-controls'
            ],
            interactions: [
                'data-refresh',
                'chart-toggle',
                'theme-switch',
                'responsive-layout'
            ]
        }
    },

    // Performance benchmarks per browser
    performanceBenchmarks: {
        pageLoadTime: {
            excellent: 1500,  // ms
            good: 2500,
            acceptable: 4000,
            poor: 6000
        },
        domReadyTime: {
            excellent: 800,   // ms
            good: 1500,
            acceptable: 2500,
            poor: 4000
        },
        chartRenderTime: {
            excellent: 300,   // ms
            good: 600,
            acceptable: 1000,
            poor: 2000
        },
        interactionDelay: {
            excellent: 50,    // ms
            good: 100,
            acceptable: 200,
            poor: 500
        }
    },

    // Responsive breakpoints
    responsiveBreakpoints: {
        mobile: {
            name: 'Mobile',
            minWidth: 320,
            maxWidth: 575,
            testViewports: [
                { width: 375, height: 667, name: 'iPhone SE' },
                { width: 414, height: 896, name: 'iPhone XR' },
                { width: 360, height: 640, name: 'Samsung Galaxy' }
            ]
        },
        tablet: {
            name: 'Tablet',
            minWidth: 576,
            maxWidth: 991,
            testViewports: [
                { width: 768, height: 1024, name: 'iPad' },
                { width: 820, height: 1180, name: 'iPad Air' },
                { width: 800, height: 1280, name: 'Galaxy Tab' }
            ]
        },
        desktop: {
            name: 'Desktop',
            minWidth: 992,
            maxWidth: 1199,
            testViewports: [
                { width: 1024, height: 768, name: 'Small Desktop' },
                { width: 1200, height: 800, name: 'Medium Desktop' }
            ]
        },
        largeDesktop: {
            name: 'Large Desktop',
            minWidth: 1200,
            maxWidth: 9999,
            testViewports: [
                { width: 1440, height: 900, name: 'MacBook Pro' },
                { width: 1920, height: 1080, name: 'Full HD' },
                { width: 2560, height: 1440, name: '2K Display' }
            ]
        }
    },

    // Accessibility requirements
    accessibilityRequirements: {
        wcag: {
            level: 'AA',
            guidelines: [
                'keyboard-navigation',
                'screen-reader-support',
                'color-contrast',
                'text-scaling',
                'focus-indicators'
            ]
        },
        aria: {
            labels: true,
            landmarks: true,
            liveRegions: true,
            descriptions: true
        }
    },

    // Error handling and recovery
    errorHandling: {
        gracefulDegradation: {
            jsDisabled: 'Basic functionality should work',
            cssDisabled: 'Content should be readable',
            slowConnection: 'Progressive loading',
            networkError: 'Offline fallback'
        },
        polyfills: {
            fetch: 'https://cdn.jsdelivr.net/npm/whatwg-fetch@3.6.19/dist/fetch.umd.js',
            promise: 'https://cdn.jsdelivr.net/npm/es6-promise@4.2.8/dist/es6-promise.auto.min.js',
            intersectionObserver: 'https://cdn.jsdelivr.net/npm/intersection-observer@0.12.2/intersection-observer.js',
            customElements: 'https://cdn.jsdelivr.net/npm/@webcomponents/custom-elements@1.6.0/custom-elements.min.js'
        }
    },

    // Test automation settings
    automation: {
        selenium: {
            browserDrivers: {
                chrome: 'chromedriver',
                firefox: 'geckodriver',
                safari: 'safaridriver',
                edge: 'msedgedriver'
            },
            testTimeout: 30000,
            implicitWait: 10000,
            pageLoadTimeout: 60000
        },
        puppeteer: {
            headless: true,
            devtools: false,
            slowMo: 0,
            timeout: 30000
        }
    },

    // Report generation
    reporting: {
        formats: ['json', 'html', 'csv'],
        includeBrowserInfo: true,
        includeScreenshots: true,
        includePerformanceMetrics: true,
        includeAccessibilityResults: true
    }
};

// Export configuration
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CrossBrowserTestConfig;
} else if (typeof window !== 'undefined') {
    window.CrossBrowserTestConfig = CrossBrowserTestConfig;
}
