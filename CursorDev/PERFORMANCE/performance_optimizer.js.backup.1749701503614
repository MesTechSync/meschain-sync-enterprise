/**
 * MesChain-Sync Performance Optimizer
 * Frontend CSS/JS Minification and Optimization Tool
 * Created: June 4, 2025 - Alt Görev 3: Performance Optimization
 */

class PerformanceOptimizer {
    constructor() {
        this.optimizationStats = {
            cssMinified: 0,
            jsCompressed: 0,
            imagesOptimized: 0,
            totalSavings: 0
        };
    }

    /**
     * CSS Minification - Remove whitespace, comments, and unused rules
     */
    minifyCSS(cssContent) {
        return cssContent
            // Remove comments
            .replace(/\/\*[\s\S]*?\*\//g, '')
            // Remove extra whitespace
            .replace(/\s+/g, ' ')
            // Remove whitespace around selectors
            .replace(/\s*{\s*/g, '{')
            .replace(/;\s*/g, ';')
            .replace(/}\s*/g, '}')
            // Remove last semicolon before }
            .replace(/;}/g, '}')
            // Remove whitespace at start/end
            .trim();
    }

    /**
     * JavaScript Minification - Basic compression
     */
    minifyJS(jsContent) {
        return jsContent
            // Remove single line comments
            .replace(/\/\/.*$/gm, '')
            // Remove multi-line comments
            .replace(/\/\*[\s\S]*?\*\//g, '')
            // Remove extra whitespace
            .replace(/\s+/g, ' ')
            // Remove whitespace around operators
            .replace(/\s*([{}();,:])\s*/g, '$1')
            // Remove whitespace at start/end
            .trim();
    }

    /**
     * Generate Critical CSS for above-the-fold content
     */
    generateCriticalCSS(htmlContent, fullCSS) {
        const criticalSelectors = new Set();
        
        // Extract all class and ID selectors from HTML
        const classMatches = htmlContent.match(/class="([^"]*)"/g) || [];
        const idMatches = htmlContent.match(/id="([^"]*)"/g) || [];
        
        classMatches.forEach(match => {
            const classes = match.replace('class="', '').replace('"', '').split(' ');
            classes.forEach(cls => cls && criticalSelectors.add(`.${cls}`));
        });
        
        idMatches.forEach(match => {
            const id = match.replace('id="', '').replace('"', '');
            id && criticalSelectors.add(`#${id}`);
        });

        // Add common critical selectors
        criticalSelectors.add('body');
        criticalSelectors.add('html');
        criticalSelectors.add('header');
        criticalSelectors.add('nav');
        criticalSelectors.add('.container');
        criticalSelectors.add('.header');
        criticalSelectors.add('.navbar');

        // Extract critical CSS rules
        let criticalCSS = '';
        const cssRules = fullCSS.split('}');
        
        cssRules.forEach(rule => {
            if (rule.trim()) {
                const selector = rule.split('{')[0]?.trim();
                if (selector) {
                    for (const criticalSelector of criticalSelectors) {
                        if (selector.includes(criticalSelector)) {
                            criticalCSS += rule + '}\n';
                            break;
                        }
                    }
                }
            }
        });

        return criticalCSS;
    }

    /**
     * Create optimized loading strategy
     */
    createLoadingStrategy() {
        return `
        <!-- Critical CSS Inline -->
        <style>
        /* Critical CSS will be inlined here */
        </style>
        
        <!-- Non-critical CSS with preload -->
        <link rel="preload" href="styles/optimized.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="styles/optimized.min.css"></noscript>
        
        <!-- JavaScript with defer -->
        <script defer src="js/optimized.min.js"></script>
        
        <!-- Preload key resources -->
        <link rel="preload" href="fonts/main.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
        <link rel="dns-prefetch" href="//fonts.googleapis.com">
        `;
    }

    /**
     * Generate Service Worker for caching
     */
    generateServiceWorker() {
        return `
// MesChain-Sync Service Worker - Performance Optimization
// Cache Strategy: Stale While Revalidate for static assets
// Network First for API calls

const CACHE_NAME = 'meschain-sync-v1';
const STATIC_ASSETS = [
    '/',
    '/css/optimized.min.css',
    '/js/optimized.min.js',
    '/images/logo.webp'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_ASSETS))
    );
});

// Fetch event - serve from cache, update in background
self.addEventListener('fetch', event => {
    if (event.request.destination === 'image') {
        // Images: Cache First
        event.respondWith(
            caches.match(event.request)
                .then(response => response || fetch(event.request))
        );
    } else if (event.request.url.includes('/api/')) {
        // API: Network First
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match(event.request))
        );
    } else {
        // Static assets: Stale While Revalidate
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    const fetchPromise = fetch(event.request)
                        .then(fetchResponse => {
                            caches.open(CACHE_NAME)
                                .then(cache => cache.put(event.request, fetchResponse.clone()));
                            return fetchResponse;
                        });
                    return response || fetchPromise;
                })
        );
    }
});
        `;
    }

    /**
     * Image optimization recommendations
     */
    getImageOptimizationScript() {
        return `
        // Image lazy loading with Intersection Observer
        const lazyImages = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
        
        // WebP support detection
        function supportsWebP() {
            return new Promise(resolve => {
                const webP = new Image();
                webP.onload = webP.onerror = () => resolve(webP.height === 2);
                webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
            });
        }
        
        supportsWebP().then(supported => {
            if (supported) {
                document.querySelectorAll('img').forEach(img => {
                    if (img.dataset.webp) {
                        img.src = img.dataset.webp;
                    }
                });
            }
        });
        `;
    }

    /**
     * Generate performance monitoring script
     */
    generatePerformanceMonitor() {
        return `
        // Performance monitoring and Web Vitals tracking
        class PerformanceMonitor {
            constructor() {
                this.metrics = {};
                this.initializeMonitoring();
            }
            
            initializeMonitoring() {
                // Core Web Vitals
                this.measureLCP();
                this.measureFID();
                this.measureCLS();
                
                // Custom metrics
                this.measurePageLoad();
                this.measureResourceTiming();
            }
            
            measureLCP() {
                new PerformanceObserver((entryList) => {
                    const entries = entryList.getEntries();
                    const lastEntry = entries[entries.length - 1];
                    this.metrics.lcp = lastEntry.startTime;
                    console.log('LCP:', lastEntry.startTime);
                }).observe({entryTypes: ['largest-contentful-paint']});
            }
            
            measureFID() {
                new PerformanceObserver((entryList) => {
                    for (const entry of entryList.getEntries()) {
                        this.metrics.fid = entry.processingStart - entry.startTime;
                        console.log('FID:', this.metrics.fid);
                    }
                }).observe({type: 'first-input', buffered: true});
            }
            
            measureCLS() {
                let clsValue = 0;
                let clsEntries = [];
                
                new PerformanceObserver((entryList) => {
                    for (const entry of entryList.getEntries()) {
                        if (!entry.hadRecentInput) {
                            clsEntries.push(entry);
                            clsValue += entry.value;
                        }
                    }
                    this.metrics.cls = clsValue;
                    console.log('CLS:', clsValue);
                }).observe({type: 'layout-shift', buffered: true});
            }
            
            measurePageLoad() {
                window.addEventListener('load', () => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    this.metrics.loadTime = perfData.loadEventEnd - perfData.fetchStart;
                    this.metrics.domReady = perfData.domContentLoadedEventEnd - perfData.fetchStart;
                    console.log('Page Load Time:', this.metrics.loadTime);
                    console.log('DOM Ready:', this.metrics.domReady);
                });
            }
            
            measureResourceTiming() {
                const resources = performance.getEntriesByType('resource');
                this.metrics.resources = resources.map(resource => ({
                    name: resource.name,
                    duration: resource.duration,
                    size: resource.transferSize
                }));
            }
            
            getMetrics() {
                return this.metrics;
            }
        }
        
        // Initialize performance monitoring
        const perfMonitor = new PerformanceMonitor();
        
        // Report metrics after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                const metrics = perfMonitor.getMetrics();
                // Send to analytics or log locally
                console.log('Performance Metrics:', metrics);
            }, 2000);
        });
        `;
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceOptimizer;
}
