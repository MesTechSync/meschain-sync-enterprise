/* 🎨 SELINAY THEME TOGGLE CONTROLLER - MesChain-Sync Enterprise */
/* Dark/Light Mode Theme Management */
/* Created: June 5, 2025 06:00 UTC */

class SelinayThemeController {
    constructor() {
        this.currentTheme = this.getStoredTheme() || 'light';
        this.toggleButton = null;
        this.transitionDuration = 300;
        
        console.log('🎨 Selinay Theme Controller Initialized');
        this.init();
    }

    init() {
        this.createToggleButton();
        this.applyTheme(this.currentTheme);
        this.bindEvents();
        this.setupKeyboardShortcut();
        
        console.log(`🌙 Current theme: ${this.currentTheme}`);
    }

    createToggleButton() {
        // Create theme toggle button
        this.toggleButton = document.createElement('button');
        this.toggleButton.className = 'theme-toggle';
        this.toggleButton.setAttribute('aria-label', 'Toggle dark/light mode');
        this.toggleButton.innerHTML = this.currentTheme === 'dark' ? '☀️' : '🌙';
        
        // Add button styles
        Object.assign(this.toggleButton.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: this.currentTheme === 'dark' ? '#3b82f6' : '#1f2937',
            border: 'none',
            borderRadius: '50%',
            width: '50px',
            height: '50px',
            color: 'white',
            fontSize: '20px',
            cursor: 'pointer',
            zIndex: '1000',
            transition: 'all 0.3s ease',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.2)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
        });

        // Add hover effect
        this.toggleButton.addEventListener('mouseenter', () => {
            this.toggleButton.style.transform = 'scale(1.1)';
            this.toggleButton.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.3)';
        });

        this.toggleButton.addEventListener('mouseleave', () => {
            this.toggleButton.style.transform = 'scale(1)';
            this.toggleButton.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.2)';
        });

        // Add to DOM
        document.body.appendChild(this.toggleButton);
    }

    bindEvents() {
        if (this.toggleButton) {
            this.toggleButton.addEventListener('click', () => this.toggleTheme());
        }

        // Listen for system theme changes
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener(() => this.handleSystemThemeChange());
        }

        // Handle page load theme application
        document.addEventListener('DOMContentLoaded', () => {
            this.applyTheme(this.currentTheme);
        });
    }

    setupKeyboardShortcut() {
        // Ctrl/Cmd + Shift + T to toggle theme
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                e.preventDefault();
                this.toggleTheme();
            }
        });
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
        
        // Animate the toggle button
        this.animateToggleButton();
        
        console.log(`🔄 Theme switched to: ${newTheme}`);
    }

    setTheme(theme) {
        this.currentTheme = theme;
        this.applyTheme(theme);
        this.saveTheme(theme);
        this.updateToggleButton(theme);
        this.notifyThemeChange(theme);
    }

    applyTheme(theme) {
        // Apply theme to document
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update meta theme-color for mobile browsers
        this.updateMetaThemeColor(theme);
        
        // Apply theme-specific styles
        this.applyThemeStyles(theme);
        
        // Update all themed elements
        this.updateThemedElements(theme);
    }

    applyThemeStyles(theme) {
        const root = document.documentElement;
        
        if (theme === 'dark') {
            // Dark theme CSS variables
            root.style.setProperty('--bg-primary', '#0a0a0a');
            root.style.setProperty('--bg-secondary', '#1a1a1a');
            root.style.setProperty('--bg-tertiary', '#2a2a2a');
            root.style.setProperty('--text-primary', '#ffffff');
            root.style.setProperty('--text-secondary', '#cccccc');
            root.style.setProperty('--text-muted', '#888888');
            root.style.setProperty('--accent-blue', '#3b82f6');
            root.style.setProperty('--accent-green', '#10b981');
            root.style.setProperty('--accent-red', '#ef4444');
            root.style.setProperty('--accent-yellow', '#f59e0b');
            root.style.setProperty('--border-color', '#333333');
            root.style.setProperty('--shadow-color', 'rgba(0, 0, 0, 0.5)');
        } else {
            // Light theme CSS variables
            root.style.setProperty('--bg-primary', '#ffffff');
            root.style.setProperty('--bg-secondary', '#f8f9fa');
            root.style.setProperty('--bg-tertiary', '#e9ecef');
            root.style.setProperty('--text-primary', '#333333');
            root.style.setProperty('--text-secondary', '#666666');
            root.style.setProperty('--text-muted', '#999999');
            root.style.setProperty('--accent-blue', '#2563eb');
            root.style.setProperty('--accent-green', '#059669');
            root.style.setProperty('--accent-red', '#dc2626');
            root.style.setProperty('--accent-yellow', '#d97706');
            root.style.setProperty('--border-color', '#dee2e6');
            root.style.setProperty('--shadow-color', 'rgba(0, 0, 0, 0.1)');
        }
    }

    updateThemedElements(theme) {
        // Update body background
        document.body.style.backgroundColor = theme === 'dark' ? '#0a0a0a' : '#ffffff';
        document.body.style.color = theme === 'dark' ? '#ffffff' : '#333333';
        
        // Update all cards and panels
        const cards = document.querySelectorAll('.card, .panel, .dashboard-widget');
        cards.forEach(card => {
            card.style.backgroundColor = theme === 'dark' ? '#1a1a1a' : '#ffffff';
            card.style.borderColor = theme === 'dark' ? '#333333' : '#dee2e6';
            card.style.color = theme === 'dark' ? '#ffffff' : '#333333';
        });

        // Update form elements
        const formElements = document.querySelectorAll('input, textarea, select');
        formElements.forEach(element => {
            element.style.backgroundColor = theme === 'dark' ? '#2a2a2a' : '#ffffff';
            element.style.borderColor = theme === 'dark' ? '#333333' : '#dee2e6';
            element.style.color = theme === 'dark' ? '#ffffff' : '#333333';
        });

        // Update navigation
        const navElements = document.querySelectorAll('.navbar, .sidebar, .nav');
        navElements.forEach(nav => {
            nav.style.backgroundColor = theme === 'dark' ? '#1a1a1a' : '#ffffff';
            nav.style.borderColor = theme === 'dark' ? '#333333' : '#dee2e6';
        });

        // Update charts if Chart.js is available
        this.updateChartThemes(theme);
    }

    updateChartThemes(theme) {
        // Update Chart.js themes if charts exist
        if (window.Chart && window.Chart.instances) {
            Object.values(window.Chart.instances).forEach(chart => {
                if (chart.options && chart.options.plugins) {
                    chart.options.plugins.legend = chart.options.plugins.legend || {};
                    chart.options.plugins.legend.labels = chart.options.plugins.legend.labels || {};
                    chart.options.plugins.legend.labels.color = theme === 'dark' ? '#ffffff' : '#333333';
                    
                    if (chart.options.scales) {
                        Object.keys(chart.options.scales).forEach(scaleKey => {
                            const scale = chart.options.scales[scaleKey];
                            if (scale.ticks) {
                                scale.ticks.color = theme === 'dark' ? '#ffffff' : '#333333';
                            }
                            if (scale.grid) {
                                scale.grid.color = theme === 'dark' ? '#333333' : '#dee2e6';
                            }
                        });
                    }
                    
                    chart.update();
                }
            });
        }
    }

    updateMetaThemeColor(theme) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        
        metaThemeColor.content = theme === 'dark' ? '#0a0a0a' : '#ffffff';
    }

    updateToggleButton(theme) {
        if (this.toggleButton) {
            this.toggleButton.innerHTML = theme === 'dark' ? '☀️' : '🌙';
            this.toggleButton.style.background = theme === 'dark' ? '#3b82f6' : '#1f2937';
            this.toggleButton.setAttribute('aria-label', 
                theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'
            );
        }
    }

    animateToggleButton() {
        if (this.toggleButton) {
            this.toggleButton.style.transform = 'scale(0.8) rotate(180deg)';
            
            setTimeout(() => {
                this.toggleButton.style.transform = 'scale(1) rotate(0deg)';
            }, 150);
        }
    }

    saveTheme(theme) {
        try {
            localStorage.setItem('selinay-theme-preference', theme);
        } catch (e) {
            console.warn('Could not save theme preference to localStorage');
        }
    }

    getStoredTheme() {
        try {
            return localStorage.getItem('selinay-theme-preference');
        } catch (e) {
            console.warn('Could not read theme preference from localStorage');
            return null;
        }
    }

    getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    handleSystemThemeChange() {
        // Only follow system theme if no user preference is stored
        const storedTheme = this.getStoredTheme();
        if (!storedTheme) {
            const systemTheme = this.getSystemTheme();
            this.setTheme(systemTheme);
        }
    }

    notifyThemeChange(theme) {
        // Dispatch custom event for other components
        const event = new CustomEvent('themeChanged', {
            detail: { theme: theme, timestamp: Date.now() }
        });
        document.dispatchEvent(event);
        
        // Update any marketplace integrations
        this.updateMarketplaceThemes(theme);
    }

    updateMarketplaceThemes(theme) {
        // Update Trendyol integration theme
        if (window.TrendyolIntegration && window.TrendyolIntegration.updateTheme) {
            window.TrendyolIntegration.updateTheme(theme);
        }
        
        // Update Hepsiburada integration theme
        if (window.HepsiburadaIntegration && window.HepsiburadaIntegration.updateTheme) {
            window.HepsiburadaIntegration.updateTheme(theme);
        }
        
        // Update N11 integration theme
        if (window.N11Integration && window.N11Integration.updateTheme) {
            window.N11Integration.updateTheme(theme);
        }
    }

    // Public API methods
    getCurrentTheme() {
        return this.currentTheme;
    }

    isDarkMode() {
        return this.currentTheme === 'dark';
    }

    isLightMode() {
        return this.currentTheme === 'light';
    }

    resetToSystemTheme() {
        const systemTheme = this.getSystemTheme();
        this.setTheme(systemTheme);
        
        // Clear stored preference
        try {
            localStorage.removeItem('selinay-theme-preference');
        } catch (e) {
            console.warn('Could not clear theme preference from localStorage');
        }
    }

    destroy() {
        if (this.toggleButton && this.toggleButton.parentNode) {
            this.toggleButton.parentNode.removeChild(this.toggleButton);
        }
        this.toggleButton = null;
    }
}

// Auto-initialize theme controller
if (typeof window !== 'undefined') {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.selinayTheme = new SelinayThemeController();
        });
    } else {
        window.selinayTheme = new SelinayThemeController();
    }
    
    // Make class available globally
    window.SelinayThemeController = SelinayThemeController;
}

export default SelinayThemeController;
