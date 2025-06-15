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
        
        // Smooth transition effect
        setTimeout(() => {
            this.isTransitioning = false;
        }, 300);
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
        // Update main theme toggle icon
        const themeIcons = document.querySelectorAll('#themeIcon');
        themeIcons.forEach(icon => {
            if (theme === 'dark') {
                icon.className = 'ph ph-moon text-lg text-blue-400';
            } else {
                icon.className = 'ph ph-sun text-lg text-yellow-500';
            }
        });
        
        // Update theme selector icon
        const themeSelectorIcons = document.querySelectorAll('#themeSelectorIcon');
        themeSelectorIcons.forEach(icon => {
            if (theme === 'dark') {
                icon.className = 'ph ph-moon text-base text-blue-400';
            } else {
                icon.className = 'ph ph-sun text-base text-yellow-500';
            }
        });
        
        const themeTexts = document.querySelectorAll('#themeText');
        themeTexts.forEach(text => {
            text.textContent = theme === 'dark' ? 'Karanlık' : 'Aydınlık';
        });
        
        // Update theme selector active states
        this.updateThemeSelector(theme);
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

// Close theme selector when clicking outside
function setupThemeClickOutside() {
    document.addEventListener('click', function(event) {
        const themeSelector = document.getElementById('themeSelector');
        const themeSelectorButton = event.target.closest('[onclick="toggleThemeSelector()"]');
        
        if (themeSelector && !themeSelectorButton && !themeSelector.contains(event.target)) {
            themeSelector.style.opacity = '0';
            themeSelector.style.visibility = 'hidden';
            themeSelector.classList.remove('visible');
        }
    });
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

// Initialize theme system
function initializeThemeSystem() {
    const themeManager = new MesChainThemeManager();
    window.themeManager = themeManager; // Make it globally accessible
    
    setupThemeClickOutside();
    
    // Update global theme reference
    if (window.MesChain) {
        window.MesChain.currentTheme = themeManager.currentTheme;
    }
}

// Make functions globally available
window.toggleTheme = toggleTheme;
window.setTheme = setTheme;
window.toggleThemeSelector = toggleThemeSelector;
window.initializeThemeSystem = initializeThemeSystem;
