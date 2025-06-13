/**
 * MesChain-Sync Theme Manager
 * Handles theme switching and persistence
 */

class MesChainThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('meschain-theme') || 'light';
        this.init();
    }

    init() {
        // Set initial theme
        this.setTheme(this.currentTheme);
        
        // Add event listeners
        this.bindEvents();
        
        // Listen for system theme changes
        this.listenSystemTheme();
        
        console.log('🌙 MesChain Theme Manager initialized');
    }

    setTheme(theme) {
        this.currentTheme = theme;
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('meschain-theme', theme);
        
        this.updateThemeUI();
        this.dispatchThemeChange();
    }

    updateThemeUI() {
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        
        if (!themeIcon || !themeText) return;
        
        if (this.currentTheme === 'dark') {
            themeIcon.className = 'ph ph-moon';
            themeText.textContent = 'Dark Mode';
        } else {
            themeIcon.className = 'ph ph-sun';
            themeText.textContent = 'Light Mode';
        }
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
        
        // Add visual feedback
        this.addToggleAnimation();
    }

    addToggleAnimation() {
        const toggle = document.getElementById('themeToggle');
        if (toggle) {
            toggle.style.transform = 'scale(0.95)';
            setTimeout(() => {
                toggle.style.transform = 'scale(1)';
            }, 150);
        }
    }

    bindEvents() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }

        // Keyboard shortcut: Ctrl/Cmd + Shift + T
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                e.preventDefault();
                this.toggleTheme();
            }
        });
    }

    listenSystemTheme() {
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            mediaQuery.addEventListener('change', (e) => {
                // Only auto-switch if user hasn't manually set a preference
                const storedTheme = localStorage.getItem('meschain-theme');
                if (!storedTheme) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }

    dispatchThemeChange() {
        // Dispatch custom event for other components to listen
        const event = new CustomEvent('themeChanged', {
            detail: { theme: this.currentTheme }
        });
        document.dispatchEvent(event);
    }

    getTheme() {
        return this.currentTheme;
    }

    // Get theme-aware colors for charts and components
    getThemeColors() {
        const isDark = this.currentTheme === 'dark';
        
        return {
            primary: isDark ? '#8b5cf6' : '#6d28d9',
            secondary: isDark ? '#a78bfa' : '#8b5cf6',
            accent: isDark ? '#3b82f6' : '#1d4ed8',
            background: isDark ? '#1e293b' : '#ffffff',
            surface: isDark ? '#374151' : '#f8f9fa',
            text: isDark ? '#f8fafc' : '#0f172a',
            textSecondary: isDark ? '#cbd5e1' : '#475569',
            border: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
            success: isDark ? '#10b981' : '#059669',
            warning: isDark ? '#f59e0b' : '#d97706',
            error: isDark ? '#ef4444' : '#dc2626'
        };
    }
}

// Initialize theme manager when DOM is ready
let themeManager;

function initThemeManager() {
    themeManager = new MesChainThemeManager();
    return themeManager;
}

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeManager);
} else {
    initThemeManager();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MesChainThemeManager;
}
