// 🎨 SELINAY THEME MANAGER - MesChain-Sync Enterprise
// Production-Ready Theme System with Performance Optimization
// Created: June 5, 2025 06:00 UTC

class SelinayThemeManager {
    constructor() {
        this.currentTheme = 'light';
        this.themes = {
            light: {
                name: 'Light Theme',
                icon: '🌞',
                colors: {
                    primary: '#3b82f6',
                    secondary: '#64748b',
                    success: '#10b981',
                    warning: '#f59e0b',
                    danger: '#ef4444',
                    background: '#ffffff',
                    surface: '#f8fafc',
                    text: '#1e293b',
                    border: '#e2e8f0'
                }
            },
            dark: {
                name: 'Dark Theme',
                icon: '🌙',
                colors: {
                    primary: '#60a5fa',
                    secondary: '#94a3b8',
                    success: '#34d399',
                    warning: '#fbbf24',
                    danger: '#f87171',
                    background: '#0f172a',
                    surface: '#1e293b',
                    text: '#f1f5f9',
                    border: '#334155'
                }
            }
        };
        
        this.init();
    }

    init() {
        this.loadSavedTheme();
        this.createToggleButton();
        this.bindEvents();
        this.applyThemeToCharts();
        this.setupPerformanceOptimization();
        
        console.log('🎨 Selinay Theme Manager initialized successfully');
    }

    loadSavedTheme() {
        const savedTheme = localStorage.getItem('selinay-theme') || 'light';
        this.setTheme(savedTheme, false);
    }

    setTheme(themeName, save = true) {
        if (!this.themes[themeName]) {
            console.warn(`Theme '${themeName}' not found, falling back to light`);
            themeName = 'light';
        }

        this.currentTheme = themeName;
        const theme = this.themes[themeName];
        
        // Apply theme to document
        document.documentElement.setAttribute('data-theme', themeName);
        
        // Update CSS custom properties for dynamic theming
        this.updateCSSProperties(theme.colors);
        
        // Update toggle button
        this.updateToggleButton();
        
        // Apply to existing charts
        this.applyThemeToCharts();
        
        // Save preference
        if (save) {
            localStorage.setItem('selinay-theme', themeName);
        }
        
        // Dispatch theme change event
        this.dispatchThemeChangeEvent(themeName);
        
        console.log(`🎨 Theme switched to: ${theme.name}`);
    }

    updateCSSProperties(colors) {
        const root = document.documentElement;
        Object.entries(colors).forEach(([key, value]) => {
            root.style.setProperty(`--theme-${key}`, value);
        });
    }

    createToggleButton() {
        // Remove existing button if any
        const existingButton = document.getElementById('theme-toggle');
        if (existingButton) {
            existingButton.remove();
        }

        const button = document.createElement('button');
        button.id = 'theme-toggle';
        button.className = 'theme-toggle accelerated';
        button.setAttribute('aria-label', 'Toggle theme');
        button.setAttribute('title', 'Switch between light and dark themes');
        
        // Add to page
        document.body.appendChild(button);
        
        this.updateToggleButton();
    }

    updateToggleButton() {
        const button = document.getElementById('theme-toggle');
        if (!button) return;
        
        const theme = this.themes[this.currentTheme];
        button.innerHTML = theme.icon;
        button.setAttribute('title', `Switch to ${this.currentTheme === 'light' ? 'dark' : 'light'} theme`);
    }

    bindEvents() {
        // Toggle button click
        document.addEventListener('click', (e) => {
            if (e.target.id === 'theme-toggle') {
                this.toggleTheme();
            }
        });

        // Keyboard shortcut (Ctrl/Cmd + Shift + T)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                e.preventDefault();
                this.toggleTheme();
            }
        });

        // System theme preference change
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                if (!localStorage.getItem('selinay-theme')) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
        
        // Add visual feedback
        this.animateToggle();
    }

    animateToggle() {
        const button = document.getElementById('theme-toggle');
        if (!button) return;
        
        button.style.transform = 'scale(0.8) rotateY(180deg)';
        
        setTimeout(() => {
            button.style.transform = 'scale(1) rotateY(0deg)';
        }, 150);
    }

    applyThemeToCharts() {
        // Apply theme to Chart.js instances
        if (window.Chart && window.Chart.instances) {
            const theme = this.themes[this.currentTheme];
            const textColor = theme.colors.text;
            const gridColor = this.currentTheme === 'dark' ? '#374151' : '#e5e7eb';
            
            Object.values(window.Chart.instances).forEach(chart => {
                if (chart.options) {
                    // Update text colors
                    if (chart.options.plugins && chart.options.plugins.legend) {
                        chart.options.plugins.legend.labels.color = textColor;
                    }
                    
                    // Update grid colors
                    if (chart.options.scales) {
                        Object.values(chart.options.scales).forEach(scale => {
                            if (scale.grid) {
                                scale.grid.color = gridColor;
                            }
                            if (scale.ticks) {
                                scale.ticks.color = textColor;
                            }
                        });
                    }
                    
                    chart.update('none'); // Update without animation for performance
                }
            });
        }
    }

    setupPerformanceOptimization() {
        // Use CSS containment for better performance
        const themeContainer = document.createElement('style');
        themeContainer.textContent = `
            [data-theme] {
                contain: layout style;
                will-change: color, background-color;
            }
            
            .theme-transition {
                transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            }
        `;
        document.head.appendChild(themeContainer);
        
        // Apply transition class to elements that should animate
        const animatedElements = document.querySelectorAll('body, .card, .btn, input, textarea, select');
        animatedElements.forEach(el => el.classList.add('theme-transition'));
    }

    dispatchThemeChangeEvent(themeName) {
        const event = new CustomEvent('themeChanged', {
            detail: {
                theme: themeName,
                colors: this.themes[themeName].colors
            }
        });
        document.dispatchEvent(event);
    }

    // Public API methods
    getCurrentTheme() {
        return this.currentTheme;
    }

    getThemeColors(themeName = this.currentTheme) {
        return this.themes[themeName]?.colors || this.themes.light.colors;
    }

    addCustomTheme(name, config) {
        this.themes[name] = config;
        console.log(`🎨 Custom theme '${name}' added successfully`);
    }

    // Accessibility helpers
    getContrastRatio(color1, color2) {
        // Simplified contrast calculation
        const getLuminance = (color) => {
            const rgb = this.hexToRgb(color);
            if (!rgb) return 0;
            
            const rsRGB = rgb.r / 255;
            const gsRGB = rgb.g / 255;
            const bsRGB = rgb.b / 255;
            
            const r = rsRGB <= 0.03928 ? rsRGB / 12.92 : Math.pow((rsRGB + 0.055) / 1.055, 2.4);
            const g = gsRGB <= 0.03928 ? gsRGB / 12.92 : Math.pow((gsRGB + 0.055) / 1.055, 2.4);
            const b = bsRGB <= 0.03928 ? bsRGB / 12.92 : Math.pow((bsRGB + 0.055) / 1.055, 2.4);
            
            return 0.2126 * r + 0.7152 * g + 0.0722 * b;
        };
        
        const lum1 = getLuminance(color1);
        const lum2 = getLuminance(color2);
        
        const brightest = Math.max(lum1, lum2);
        const darkest = Math.min(lum1, lum2);
        
        return (brightest + 0.05) / (darkest + 0.05);
    }

    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    // Performance monitoring
    measureThemeChangePerformance() {
        const startTime = performance.now();
        return {
            end: () => {
                const endTime = performance.now();
                const duration = endTime - startTime;
                console.log(`🎨 Theme change completed in ${duration.toFixed(2)}ms`);
                return duration;
            }
        };
    }

    // Cleanup method
    destroy() {
        const toggleButton = document.getElementById('theme-toggle');
        if (toggleButton) {
            toggleButton.remove();
        }
        
        // Remove event listeners
        document.removeEventListener('click', this.handleClick);
        document.removeEventListener('keydown', this.handleKeydown);
        
        console.log('🎨 Selinay Theme Manager destroyed');
    }
}

// Performance Monitoring Class
class SelinayPerformanceMonitor {
    constructor() {
        this.metrics = {
            pageLoad: 0,
            firstPaint: 0,
            firstContentfulPaint: 0,
            largestContentfulPaint: 0,
            cumulativeLayoutShift: 0,
            firstInputDelay: 0,
            totalBlockingTime: 0
        };
        
        this.init();
    }

    init() {
        this.measurePageLoad();
        this.measureWebVitals();
        this.createPerformanceDisplay();
        this.startRealtimeMonitoring();
        
        console.log('📊 Selinay Performance Monitor initialized');
    }

    measurePageLoad() {
        window.addEventListener('load', () => {
            const navTiming = performance.getEntriesByType('navigation')[0];
            if (navTiming) {
                this.metrics.pageLoad = navTiming.loadEventEnd - navTiming.loadEventStart;
            }
        });
    }

    measureWebVitals() {
        // First Paint
        const paintEntries = performance.getEntriesByType('paint');
        const firstPaint = paintEntries.find(entry => entry.name === 'first-paint');
        const firstContentfulPaint = paintEntries.find(entry => entry.name === 'first-contentful-paint');
        
        if (firstPaint) {
            this.metrics.firstPaint = firstPaint.startTime;
        }
        
        if (firstContentfulPaint) {
            this.metrics.firstContentfulPaint = firstContentfulPaint.startTime;
        }

        // Use PerformanceObserver for newer metrics
        if ('PerformanceObserver' in window) {
            // Largest Contentful Paint
            const lcpObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const lastEntry = entries[entries.length - 1];
                this.metrics.largestContentfulPaint = lastEntry.startTime;
            });
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });

            // Cumulative Layout Shift
            const clsObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    if (!entry.hadRecentInput) {
                        this.metrics.cumulativeLayoutShift += entry.value;
                    }
                }
            });
            clsObserver.observe({ entryTypes: ['layout-shift'] });

            // First Input Delay
            const fidObserver = new PerformanceObserver((entryList) => {
                for (const entry of entryList.getEntries()) {
                    this.metrics.firstInputDelay = entry.processingStart - entry.startTime;
                }
            });
            fidObserver.observe({ entryTypes: ['first-input'] });
        }
    }

    createPerformanceDisplay() {
        // Create performance indicator (only in development)
        if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev')) {
            const display = document.createElement('div');
            display.id = 'selinay-performance-display';
            display.style.cssText = `
                position: fixed;
                bottom: 20px;
                left: 20px;
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 10px;
                border-radius: 8px;
                font-family: monospace;
                font-size: 12px;
                z-index: 10000;
                min-width: 200px;
                backdrop-filter: blur(10px);
            `;
            
            document.body.appendChild(display);
            this.updatePerformanceDisplay();
        }
    }

    updatePerformanceDisplay() {
        const display = document.getElementById('selinay-performance-display');
        if (!display) return;

        const memoryInfo = performance.memory ? {
            used: Math.round(performance.memory.usedJSHeapSize / 1048576),
            total: Math.round(performance.memory.totalJSHeapSize / 1048576),
            limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576)
        } : null;

        display.innerHTML = `
            <strong>🚀 Selinay Performance</strong><br>
            FCP: ${Math.round(this.metrics.firstContentfulPaint)}ms<br>
            LCP: ${Math.round(this.metrics.largestContentfulPaint)}ms<br>
            CLS: ${this.metrics.cumulativeLayoutShift.toFixed(3)}<br>
            FID: ${Math.round(this.metrics.firstInputDelay)}ms<br>
            ${memoryInfo ? `Memory: ${memoryInfo.used}MB/${memoryInfo.total}MB` : ''}
        `;
    }

    startRealtimeMonitoring() {
        setInterval(() => {
            this.updatePerformanceDisplay();
        }, 2000);
    }

    getPerformanceScore() {
        let score = 100;
        
        // FCP scoring
        if (this.metrics.firstContentfulPaint > 3000) score -= 20;
        else if (this.metrics.firstContentfulPaint > 1800) score -= 10;
        
        // LCP scoring
        if (this.metrics.largestContentfulPaint > 4000) score -= 25;
        else if (this.metrics.largestContentfulPaint > 2500) score -= 15;
        
        // CLS scoring
        if (this.metrics.cumulativeLayoutShift > 0.25) score -= 25;
        else if (this.metrics.cumulativeLayoutShift > 0.1) score -= 15;
        
        // FID scoring
        if (this.metrics.firstInputDelay > 300) score -= 20;
        else if (this.metrics.firstInputDelay > 100) score -= 10;
        
        return Math.max(0, score);
    }

    generateReport() {
        return {
            timestamp: new Date().toISOString(),
            metrics: { ...this.metrics },
            score: this.getPerformanceScore(),
            recommendations: this.getRecommendations()
        };
    }

    getRecommendations() {
        const recommendations = [];
        
        if (this.metrics.firstContentfulPaint > 1800) {
            recommendations.push('Optimize First Contentful Paint: Consider reducing server response time and eliminating render-blocking resources');
        }
        
        if (this.metrics.largestContentfulPaint > 2500) {
            recommendations.push('Improve Largest Contentful Paint: Optimize images and preload important resources');
        }
        
        if (this.metrics.cumulativeLayoutShift > 0.1) {
            recommendations.push('Reduce Cumulative Layout Shift: Add size attributes to images and reserve space for dynamic content');
        }
        
        if (this.metrics.firstInputDelay > 100) {
            recommendations.push('Minimize First Input Delay: Break up long tasks and optimize JavaScript execution');
        }
        
        return recommendations;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize theme manager
    window.selinayThemeManager = new SelinayThemeManager();
    
    // Initialize performance monitor
    window.selinayPerformanceMonitor = new SelinayPerformanceMonitor();
    
    // Global utility functions
    window.selinayUtils = {
        // Theme utilities
        toggleTheme: () => window.selinayThemeManager.toggleTheme(),
        getCurrentTheme: () => window.selinayThemeManager.getCurrentTheme(),
        getThemeColors: () => window.selinayThemeManager.getThemeColors(),
        
        // Performance utilities
        getPerformanceScore: () => window.selinayPerformanceMonitor.getPerformanceScore(),
        getPerformanceReport: () => window.selinayPerformanceMonitor.generateReport(),
        
        // Utility functions
        debounce: (func, wait) => {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        throttle: (func, limit) => {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },
        
        // Lazy loading helper
        lazyLoad: (selector, callback) => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        callback(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            document.querySelectorAll(selector).forEach(el => {
                observer.observe(el);
            });
        }
    };
    
    console.log('🎉 Selinay Theme & Performance System ready!');
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SelinayThemeManager, SelinayPerformanceMonitor };
}
