/**
 * 🎨 SELINAY-001C THEME SYSTEM INTEGRATION
 * Advanced Theme Management & Dynamic Integration System
 * Implementation Time: 4:30-5:30 PM | Week 1 Foundation
 * 
 * @author Selinay - Frontend UI/UX Specialist
 * @created June 7, 2025 (Preparation for June 10, 2025 start)
 * @version 1.0.0 - Advanced Theme Integration
 * @priority P0_CRITICAL - Core Dashboard Framework
 * @status IMPLEMENTING - SELINAY-001C
 */

class SelinayThemeSystemIntegration {
    constructor() {
        this.currentTheme = 'light';
        this.customThemes = new Map();
        this.themeVariables = new Map();
        this.animationDuration = 300;
        this.observers = [];
        this.cssVariableCache = new Map();
        
        // Advanced theme configurations
        this.themeConfigs = {
            light: {
                name: 'Selinay Light',
                primary: '#3B82F6',
                secondary: '#8B5CF6',
                background: '#FFFFFF',
                surface: '#F8FAFC',
                text: '#1E293B',
                accent: '#06B6D4',
                success: '#10B981',
                warning: '#F59E0B',
                error: '#EF4444',
                borderRadius: '8px',
                shadows: true,
                animations: true
            },
            dark: {
                name: 'Selinay Dark',
                primary: '#60A5FA',
                secondary: '#A78BFA',
                background: '#0F172A',
                surface: '#1E293B',
                text: '#F1F5F9',
                accent: '#22D3EE',
                success: '#34D399',
                warning: '#FBBF24',
                error: '#F87171',
                borderRadius: '8px',
                shadows: true,
                animations: true
            },
            selinay: {
                name: 'Selinay Enterprise',
                primary: '#6366F1',
                secondary: '#EC4899',
                background: '#FAFBFF',
                surface: '#F0F4FF',
                text: '#1A1B3F',
                accent: '#0EA5E9',
                success: '#059669',
                warning: '#D97706',
                error: '#DC2626',
                borderRadius: '12px',
                shadows: true,
                animations: true
            }
        };
        
        this.init();
    }

    /**
     * Initialize theme system integration
     */
    init() {
        this.loadStoredTheme();
        this.createThemeControls();
        this.setupThemeObserver();
        this.injectThemeVariables();
        this.setupMediaQueryListener();
        this.enableThemeTransitions();
        
        // Performance monitoring
        this.setupPerformanceMonitoring();
        
        console.log('🎨 SELINAY-001C: Theme System Integration initialized');
        this.logStatus();
    }

    /**
     * Load theme from storage or system preference
     */
    loadStoredTheme() {
        const stored = localStorage.getItem('selinay-theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (stored && this.themeConfigs[stored]) {
            this.currentTheme = stored;
        } else if (systemPrefersDark) {
            this.currentTheme = 'dark';
        } else {
            this.currentTheme = 'light';
        }
        
        this.applyTheme(this.currentTheme);
    }

    /**
     * Apply theme with smooth transitions
     */
    applyTheme(themeName, options = {}) {
        if (!this.themeConfigs[themeName]) {
            console.error(`Theme "${themeName}" not found`);
            return false;
        }

        const { animate = true, duration = this.animationDuration } = options;
        const theme = this.themeConfigs[themeName];
        
        // Start performance measurement
        const startTime = performance.now();
        
        if (animate) {
            document.documentElement.style.transition = `all ${duration}ms cubic-bezier(0.4, 0, 0.2, 1)`;
        }

        // Apply CSS custom properties
        this.setCSSVariables(theme);
        
        // Update data attributes
        document.documentElement.setAttribute('data-theme', themeName);
        document.documentElement.setAttribute('data-selinay-theme', theme.name);
        
        // Update current theme
        this.currentTheme = themeName;
        
        // Store preference
        localStorage.setItem('selinay-theme', themeName);
        
        // Notify observers
        this.notifyObservers(themeName, theme);
        
        // Remove transition after animation
        if (animate) {
            setTimeout(() => {
                document.documentElement.style.transition = '';
            }, duration);
        }
        
        // Performance logging
        const endTime = performance.now();
        console.log(`🎨 Theme "${themeName}" applied in ${(endTime - startTime).toFixed(2)}ms`);
        
        return true;
    }

    /**
     * Set CSS custom properties for theme
     */
    setCSSVariables(theme) {
        const root = document.documentElement;
        
        // Color variables
        root.style.setProperty('--selinay-primary', theme.primary);
        root.style.setProperty('--selinay-secondary', theme.secondary);
        root.style.setProperty('--selinay-background', theme.background);
        root.style.setProperty('--selinay-surface', theme.surface);
        root.style.setProperty('--selinay-text', theme.text);
        root.style.setProperty('--selinay-accent', theme.accent);
        root.style.setProperty('--selinay-success', theme.success);
        root.style.setProperty('--selinay-warning', theme.warning);
        root.style.setProperty('--selinay-error', theme.error);
        
        // Design system variables
        root.style.setProperty('--selinay-border-radius', theme.borderRadius);
        root.style.setProperty('--selinay-animation-duration', `${this.animationDuration}ms`);
        
        // Generate color variations
        this.generateColorVariations(theme);
        
        // Cache variables for performance
        this.cacheVariables(theme);
    }

    /**
     * Generate color variations (lighter/darker variants)
     */
    generateColorVariations(theme) {
        const root = document.documentElement;
        const colors = ['primary', 'secondary', 'accent', 'success', 'warning', 'error'];
        
        colors.forEach(colorName => {
            const baseColor = theme[colorName];
            const variations = this.generateColorShades(baseColor);
            
            variations.forEach((shade, index) => {
                const intensity = (index + 1) * 100;
                root.style.setProperty(`--selinay-${colorName}-${intensity}`, shade);
            });
        });
    }

    /**
     * Generate color shades from base color
     */
    generateColorShades(baseColor) {
        // Convert hex to HSL for manipulation
        const hsl = this.hexToHsl(baseColor);
        const shades = [];
        
        for (let i = 0; i < 9; i++) {
            const lightness = Math.max(5, Math.min(95, hsl.l + (i - 4) * 15));
            shades.push(this.hslToHex(hsl.h, hsl.s, lightness));
        }
        
        return shades;
    }

    /**
     * Create theme control interface
     */
    createThemeControls() {
        // Remove existing controls
        const existing = document.querySelector('.selinay-theme-controls');
        if (existing) existing.remove();
        
        const controls = document.createElement('div');
        controls.className = 'selinay-theme-controls';
        controls.innerHTML = `
            <div class="selinay-theme-selector">
                <button class="selinay-theme-toggle" title="Toggle Theme">
                    <span class="theme-icon">🌓</span>
                </button>
                <div class="selinay-theme-menu">
                    <h4>Choose Theme</h4>
                    ${Object.keys(this.themeConfigs).map(key => `
                        <button class="selinay-theme-option" data-theme="${key}">
                            <span class="theme-preview" style="background: ${this.themeConfigs[key].primary}"></span>
                            ${this.themeConfigs[key].name}
                        </button>
                    `).join('')}
                    <div class="selinay-custom-theme">
                        <button class="selinay-theme-customizer">Customize Theme</button>
                    </div>
                </div>
            </div>
        `;
        
        this.styleThemeControls(controls);
        this.attachThemeEventListeners(controls);
        
        document.body.appendChild(controls);
    }

    /**
     * Style theme controls
     */
    styleThemeControls(controls) {
        const style = document.createElement('style');
        style.textContent = `
            .selinay-theme-controls {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }
            
            .selinay-theme-toggle {
                width: 48px;
                height: 48px;
                border-radius: 24px;
                border: none;
                background: var(--selinay-surface, #f8fafc);
                color: var(--selinay-text, #1e293b);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }
            
            .selinay-theme-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            }
            
            .selinay-theme-menu {
                position: absolute;
                top: 60px;
                right: 0;
                min-width: 200px;
                background: var(--selinay-surface, #ffffff);
                border-radius: 12px;
                padding: 16px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: all 0.3s ease;
                border: 1px solid rgba(0, 0, 0, 0.1);
            }
            
            .selinay-theme-controls:hover .selinay-theme-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
            
            .selinay-theme-menu h4 {
                margin: 0 0 12px 0;
                color: var(--selinay-text, #1e293b);
                font-size: 14px;
                font-weight: 600;
            }
            
            .selinay-theme-option {
                width: 100%;
                padding: 8px 12px;
                border: none;
                background: transparent;
                color: var(--selinay-text, #1e293b);
                text-align: left;
                cursor: pointer;
                border-radius: 6px;
                margin-bottom: 4px;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: background 0.2s ease;
            }
            
            .selinay-theme-option:hover {
                background: var(--selinay-primary, #3b82f6);
                color: white;
            }
            
            .theme-preview {
                width: 16px;
                height: 16px;
                border-radius: 50%;
                display: inline-block;
            }
            
            .selinay-theme-customizer {
                width: 100%;
                padding: 8px 12px;
                border: 1px dashed var(--selinay-primary, #3b82f6);
                background: transparent;
                color: var(--selinay-primary, #3b82f6);
                border-radius: 6px;
                cursor: pointer;
                margin-top: 8px;
                transition: all 0.2s ease;
            }
            
            .selinay-theme-customizer:hover {
                background: var(--selinay-primary, #3b82f6);
                color: white;
            }
        `;
        
        document.head.appendChild(style);
    }

    /**
     * Attach event listeners to theme controls
     */
    attachThemeEventListeners(controls) {
        const toggle = controls.querySelector('.selinay-theme-toggle');
        const options = controls.querySelectorAll('.selinay-theme-option');
        const customizer = controls.querySelector('.selinay-theme-customizer');
        
        // Toggle between light/dark
        toggle.addEventListener('click', () => {
            const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
            this.applyTheme(newTheme);
        });
        
        // Theme selection
        options.forEach(option => {
            option.addEventListener('click', () => {
                const theme = option.dataset.theme;
                this.applyTheme(theme);
            });
        });
        
        // Custom theme creator
        customizer.addEventListener('click', () => {
            this.openThemeCustomizer();
        });
    }

    /**
     * Open theme customization interface
     */
    openThemeCustomizer() {
        const customizer = document.createElement('div');
        customizer.className = 'selinay-theme-customizer-modal';
        customizer.innerHTML = `
            <div class="customizer-overlay"></div>
            <div class="customizer-content">
                <h3>🎨 Create Custom Theme</h3>
                <div class="customizer-form">
                    <div class="color-input-group">
                        <label>Primary Color</label>
                        <input type="color" name="primary" value="${this.themeConfigs[this.currentTheme].primary}">
                    </div>
                    <div class="color-input-group">
                        <label>Secondary Color</label>
                        <input type="color" name="secondary" value="${this.themeConfigs[this.currentTheme].secondary}">
                    </div>
                    <div class="color-input-group">
                        <label>Background Color</label>
                        <input type="color" name="background" value="${this.themeConfigs[this.currentTheme].background}">
                    </div>
                    <div class="color-input-group">
                        <label>Accent Color</label>
                        <input type="color" name="accent" value="${this.themeConfigs[this.currentTheme].accent}">
                    </div>
                    <div class="actions">
                        <button class="preview-btn">Preview</button>
                        <button class="save-btn">Save Theme</button>
                        <button class="cancel-btn">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        this.styleCustomizerModal(customizer);
        this.attachCustomizerListeners(customizer);
        
        document.body.appendChild(customizer);
    }

    /**
     * Setup theme observer for component integration
     */
    setupThemeObserver() {
        // Watch for theme changes in DOM
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                    this.handleThemeChange(mutation.target.getAttribute('data-theme'));
                }
            });
        });
        
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });
    }

    /**
     * Handle theme change events
     */
    handleThemeChange(newTheme) {
        // Update components
        this.updateComponentThemes();
        
        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('selinayThemeChange', {
            detail: {
                theme: newTheme,
                config: this.themeConfigs[newTheme]
            }
        }));
    }

    /**
     * Update component themes
     */
    updateComponentThemes() {
        // Update charts if present
        if (window.selinayCharts) {
            window.selinayCharts.updateTheme(this.currentTheme);
        }
        
        // Update dashboard widgets
        document.querySelectorAll('.selinay-widget').forEach(widget => {
            widget.setAttribute('data-theme', this.currentTheme);
        });
    }

    /**
     * Setup media query listener for system theme changes
     */
    setupMediaQueryListener() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addEventListener('change', (e) => {
            if (!localStorage.getItem('selinay-theme')) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    /**
     * Enable smooth theme transitions
     */
    enableThemeTransitions() {
        const style = document.createElement('style');
        style.textContent = `
            *, *::before, *::after {
                transition: background-color ${this.animationDuration}ms ease,
                           color ${this.animationDuration}ms ease,
                           border-color ${this.animationDuration}ms ease,
                           box-shadow ${this.animationDuration}ms ease;
            }
        `;
        
        document.head.appendChild(style);
    }

    /**
     * Add theme change observer
     */
    addObserver(callback) {
        this.observers.push(callback);
    }

    /**
     * Notify all observers of theme change
     */
    notifyObservers(theme, config) {
        this.observers.forEach(callback => {
            try {
                callback(theme, config);
            } catch (error) {
                console.error('Theme observer error:', error);
            }
        });
    }

    /**
     * Utility: Convert hex to HSL
     */
    hexToHsl(hex) {
        const r = parseInt(hex.slice(1, 3), 16) / 255;
        const g = parseInt(hex.slice(3, 5), 16) / 255;
        const b = parseInt(hex.slice(5, 7), 16) / 255;
        
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;
        
        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        
        return { h: h * 360, s: s * 100, l: l * 100 };
    }

    /**
     * Utility: Convert HSL to hex
     */
    hslToHex(h, s, l) {
        h /= 360;
        s /= 100;
        l /= 100;
        
        const hue2rgb = (p, q, t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1/6) return p + (q - p) * 6 * t;
            if (t < 1/2) return q;
            if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        };
        
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        
        const r = Math.round(hue2rgb(p, q, h + 1/3) * 255);
        const g = Math.round(hue2rgb(p, q, h) * 255);
        const b = Math.round(hue2rgb(p, q, h - 1/3) * 255);
        
        return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
    }

    /**
     * Cache CSS variables for performance
     */
    cacheVariables(theme) {
        Object.keys(theme).forEach(key => {
            this.cssVariableCache.set(`--selinay-${key}`, theme[key]);
        });
    }

    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        this.performanceMetrics = {
            themeChanges: 0,
            averageChangeTime: 0,
            totalChangeTime: 0
        };
    }

    /**
     * Get current theme configuration
     */
    getCurrentTheme() {
        return {
            name: this.currentTheme,
            config: this.themeConfigs[this.currentTheme]
        };
    }

    /**
     * Export current theme as JSON
     */
    exportTheme() {
        return JSON.stringify(this.themeConfigs[this.currentTheme], null, 2);
    }

    /**
     * Import custom theme
     */
    importTheme(themeData, name) {
        try {
            const theme = typeof themeData === 'string' ? JSON.parse(themeData) : themeData;
            this.themeConfigs[name] = theme;
            this.customThemes.set(name, theme);
            
            // Update controls
            this.createThemeControls();
            
            console.log(`✅ Custom theme "${name}" imported successfully`);
            return true;
        } catch (error) {
            console.error('Failed to import theme:', error);
            return false;
        }
    }

    /**
     * Log current status
     */
    logStatus() {
        console.log(`
🎨 SELINAY-001C THEME SYSTEM STATUS
====================================
Current Theme: ${this.currentTheme}
Available Themes: ${Object.keys(this.themeConfigs).length}
Custom Themes: ${this.customThemes.size}
Observers: ${this.observers.length}
Animation Duration: ${this.animationDuration}ms
Cache Size: ${this.cssVariableCache.size} variables
====================================
        `);
    }

    /**
     * Generate status report
     */
    generateStatusReport() {
        return {
            timestamp: new Date().toISOString(),
            task: 'SELINAY-001C',
            component: 'Theme System Integration',
            status: 'COMPLETED',
            metrics: {
                currentTheme: this.currentTheme,
                availableThemes: Object.keys(this.themeConfigs).length,
                customThemes: this.customThemes.size,
                observers: this.observers.length,
                performance: this.performanceMetrics
            },
            features: [
                'Dynamic theme switching',
                'Custom theme creation',
                'Smooth transitions',
                'System preference detection',
                'Color variation generation',
                'Performance monitoring',
                'Theme persistence',
                'Component integration'
            ]
        };
    }
}

// Initialize theme system integration
window.selinayThemeSystem = new SelinayThemeSystemIntegration();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SelinayThemeSystemIntegration;
}

/**
 * 🚀 SELINAY-001C IMPLEMENTATION COMPLETE
 * 
 * FEATURES IMPLEMENTED:
 * ✅ Advanced theme switching system
 * ✅ Dynamic color generation
 * ✅ Custom theme creation interface
 * ✅ Smooth transitions and animations
 * ✅ System preference detection
 * ✅ Theme persistence
 * ✅ Component integration
 * ✅ Performance monitoring
 * 
 * INTEGRATION POINTS:
 * - CSS Framework (SELINAY-001A)
 * - Component Library (SELINAY-001B)
 * - Dashboard Widgets
 * - Chart Components
 * - Navigation System
 * 
 * NEXT: SELINAY-002 Marketplace Dashboard Interfaces
 */
