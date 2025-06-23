/**
 * MesChain-Sync Super Admin Panel - Theme Management Module
 * Version: 4.1
 * Description: Advanced theme management system with dark/light mode support
 */

// Advanced Theme Manager Class
class MesChainThemeManager {
    constructor() {
        this.currentTheme = this.getStoredTheme() || this.getSystemTheme();
        this.isTransitioning = false;
        this.init();
    }
    
    init() {
        this.detectSystemTheme();
        this.setupEventListeners();
        this.applyInitialTheme();
    }
    
    getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }
    
    getStoredTheme() {
        try {
            return localStorage.getItem('meschain-theme') || null;
        } catch (error) {
            return null;
        }
    }
    
    setTheme(theme) {
        if (this.isTransitioning) return;
        
        this.currentTheme = theme;
        this.applyTheme(theme);
        this.saveTheme(theme);
        this.updateThemeIcon(theme);
        this.closeThemeSelector();
    }
    
    closeThemeSelector() {
        const themeSelector = document.getElementById('themeSelector');
        if (themeSelector) {
            themeSelector.style.opacity = '0';
            themeSelector.style.visibility = 'hidden';
            themeSelector.classList.remove('visible');
        }
    }
    
    toggleTheme() {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }
    
    applyTheme(theme) {
        this.isTransitioning = true;
        
        // Apply theme change with requestAnimationFrame for better performance
        requestAnimationFrame(() => {
            // Add theme transition class for smooth transitions
            document.documentElement.classList.add('theme-transition-active');
            
            document.documentElement.setAttribute('data-theme', theme);
            document.body.className = document.body.className.replace(/theme-\w+/g, '').replace(/dark/g, '');
            document.body.classList.add(`theme-${theme}`);
            
            // Add dark class for compatibility
            if (theme === 'dark') {
                document.body.classList.add('dark');
                document.documentElement.classList.add('dark');
            } else {
                document.body.classList.remove('dark');
                document.documentElement.classList.remove('dark');
            }
            
            // Create theme switch ripple effect at cursor position
            this.createThemeRippleEffect();
            
            // Remove transition class after animation completes
            setTimeout(() => {
                document.documentElement.classList.remove('theme-transition-active');
                this.isTransitioning = false;
            }, 400);
        });
    }
    
    saveTheme(theme) {
        try {
            localStorage.setItem('meschain-theme', theme);
            if (window.MesChain) {
                window.MesChain.currentTheme = theme;
            }
        } catch (error) {
            // Silently handle storage errors
        }
    }
    
    updateThemeIcon(theme) {
        const isDark = theme === 'dark';
        const iconClass = isDark ? 'ph ph-moon' : 'ph ph-sun';
        const colorClass = isDark ? 'text-blue-400' : 'text-yellow-500';
        const themeName = isDark ? 'Karanlık' : 'Aydınlık';
        
        // Update icons using a single query selector pattern for better performance
        const updateElements = (selector, classPrefix, extraClass = '') => {
            document.querySelectorAll(selector).forEach(el => {
                el.className = `${iconClass} ${classPrefix} ${colorClass} ${extraClass}`.trim();
            });
        };
        
        // Use optimized selector pattern
        updateElements('#themeIcon', 'text-lg');
        updateElements('#themeSelectorIcon', 'text-base');
        
        // Update text elements
        document.querySelectorAll('#themeText').forEach(text => {
            text.textContent = themeName;
        });
        
        // Update theme selector active states
        this.updateThemeSelector(theme);
        
        // Show notification after theme change
        this.showThemeNotification(theme);
    }
    
    updateThemeSelector(theme) {
        // Remove active state from all theme options
        const themeOptions = document.querySelectorAll('.theme-option');
        themeOptions.forEach(option => {
            option.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/30');
            option.classList.add('border-transparent');
        });
        
        // Add active state to current theme
        const activeThemeButton = document.querySelector(`[onclick="setTheme('${theme}')"]`);
        if (activeThemeButton) {
            activeThemeButton.classList.remove('border-transparent');
            activeThemeButton.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/30');
        }
    }
    
    setupEventListeners() {
        // Theme toggle button
        document.addEventListener('click', (e) => {
            if (e.target.closest('#themeToggle') || e.target.closest('.theme-toggle')) {
                e.preventDefault();
                this.toggleTheme();
            }
        });
        
        // System theme change detection
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.currentTheme === 'auto') {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }
    
    detectSystemTheme() {
        if (window.matchMedia) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            this.systemTheme = darkModeQuery.matches ? 'dark' : 'light';
        } else {
            this.systemTheme = 'light';
        }
    }
    
    applyInitialTheme() {
        this.applyTheme(this.currentTheme);
        this.updateThemeIcon(this.currentTheme);
    }
}

// Theme utility functions
function toggleTheme() {
    if (window.themeManager) {
        window.themeManager.toggleTheme();
    }
}

function setTheme(theme) {
    if (window.themeManager) {
        window.themeManager.setTheme(theme);
    }
}

function toggleThemeSelector() {
    const selector = document.getElementById('themeSelector');
    if (selector) {
        const isVisible = selector.style.opacity === '1' || selector.classList.contains('visible');
        if (isVisible) {
            selector.style.opacity = '0';
            selector.style.visibility = 'hidden';
            selector.classList.remove('visible');
        } else {
            selector.style.opacity = '1';
            selector.style.visibility = 'visible';
            selector.classList.add('visible');
        }
    }
}

// Close theme selector when clicking outside - optimized with debounce
function setupThemeClickOutside() {
    // Debounce function to improve performance
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    
    // Use passive: true for better scroll performance
    document.addEventListener('click', debounce(function(event) {
        const themeSelector = document.getElementById('themeSelector');
        const themeSelectorButton = event.target.closest('[onclick="toggleThemeSelector()"]');
        
        if (themeSelector && !themeSelectorButton && !themeSelector.contains(event.target)) {
            // Use classList for better performance
            themeSelector.classList.add('hiding');
            themeSelector.classList.remove('visible');
            
            // Remove hiding class after transition
            setTimeout(() => {
                themeSelector.style.opacity = '0';
                themeSelector.style.visibility = 'hidden';
                themeSelector.classList.remove('hiding');
            }, 300);
        }
    }, 50), { passive: true });
}

// Legacy theme function for compatibility
function setThemeLegacy(theme) {
    const html = document.documentElement;
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    
    if (theme === 'dark') {
        html.classList.add('dark');
        if (themeIcon) {
            themeIcon.className = 'ph ph-moon text-xl text-blue-400';
        }
        if (themeText) {
            themeText.setAttribute('data-tr', 'Karanlık');
            themeText.setAttribute('data-en', 'Dark');
            if (typeof applyTranslations === 'function') {
                applyTranslations();
            }
        }
    } else {
        html.classList.remove('dark');
        if (themeIcon) {
            themeIcon.className = 'ph ph-sun text-xl text-yellow-500';
        }
        if (themeText) {
            themeText.setAttribute('data-tr', 'Aydınlık');
            themeText.setAttribute('data-en', 'Light');
            if (typeof applyTranslations === 'function') {
                applyTranslations();
            }
        }
    }
    
    // Show notification
    const themeMsg = theme === 'dark' ? 'Karanlık mod aktif' : 'Aydınlık mod aktif';
    if (typeof showNotification === 'function') {
        showNotification(themeMsg, 'info');
    }
}

// Initialize theme system with performance optimization
function initializeThemeSystem() {
    // Add preload class to prevent transition flicker on page load
    document.documentElement.classList.add('preload');
    
    const themeManager = new MesChainThemeManager();
    window.themeManager = themeManager; // Make it globally accessible
    
    setupThemeClickOutside();
    
    // Update global theme reference
    if (window.MesChain) {
        window.MesChain.currentTheme = themeManager.currentTheme;
    }
    
    // Remove preload class after a brief delay to enable transitions
    setTimeout(() => {
        document.documentElement.classList.remove('preload');
    }, 100);
    
    // Log performance timing
    if (window.performance && window.performance.measure) {
        window.performance.measure('themeInitialized', 'themeJsLoaded');
        console.log('Theme initialization completed');
    }
}

// Add new methods to the MesChainThemeManager class
MesChainThemeManager.prototype.createThemeRippleEffect = function() {
    const lastClick = window.lastClickPosition || { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    
    // Create ripple element
    const ripple = document.createElement('div');
    ripple.className = 'theme-switch-ripple';
    ripple.style.top = `${lastClick.y}px`;
    ripple.style.left = `${lastClick.x}px`;
    document.body.appendChild(ripple);
    
    // Remove ripple after animation
    setTimeout(() => ripple.remove(), 1000);
};

MesChainThemeManager.prototype.showThemeNotification = function(theme) {
    // Create theme notification
    const existing = document.querySelector('.theme-switch-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = 'theme-switch-notification';
    const isDark = theme === 'dark';
    
    notification.innerHTML = `
        <i class="ph ${isDark ? 'ph-moon-stars' : 'ph-sun'} icon"></i>
        <span>${isDark ? 'Karanlık mod etkinleştirildi' : 'Aydınlık mod etkinleştirildi'}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification with slight delay
    setTimeout(() => notification.classList.add('active'), 10);
    
    // Remove notification
    setTimeout(() => {
        notification.classList.remove('active');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};

// Track mouse position for ripple effect
document.addEventListener('mousedown', (e) => {
    window.lastClickPosition = { x: e.clientX, y: e.clientY };
});

// Make functions globally available - use a more efficient approach
const globalFunctions = { toggleTheme, setTheme, toggleThemeSelector, initializeThemeSystem };
Object.entries(globalFunctions).forEach(([key, value]) => window[key] = value);

// Add performance monitoring
if (window.performance && window.performance.mark) {
    window.performance.mark('themeJsLoaded');
}
