/* 🎯 MESCHAIN-SYNC HEADER FUNCTIONALITY
   ✨ Dropdown menus, theme switching, language selection
   🎨 Interactive header components */

class MeschainHeader {
    constructor() {
        this.currentLanguage = 'tr';
        this.currentTheme = 'light';
        this.sessionTimer = null;
        this.startSessionTimer();
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Theme toggle
        document.addEventListener('click', (e) => {
            if (e.target.closest('#themeToggle')) {
                this.toggleTheme();
            }
        });

        // Language dropdown
        document.addEventListener('mouseenter', (e) => {
            if (e.target.closest('.language-dropdown')) {
                this.showLanguageMenu();
            }
        });

        document.addEventListener('mouseleave', (e) => {
            if (e.target.closest('.language-dropdown')) {
                this.hideLanguageMenu();
            }
        });

        // Notification dropdown
        document.addEventListener('mouseenter', (e) => {
            if (e.target.closest('.notification-dropdown')) {
                this.showNotificationMenu();
            }
        });

        document.addEventListener('mouseleave', (e) => {
            if (e.target.closest('.notification-dropdown')) {
                this.hideNotificationMenu();
            }
        });

        // Settings dropdown
        document.addEventListener('mouseenter', (e) => {
            if (e.target.closest('.settings-dropdown')) {
                this.showSettingsMenu();
            }
        });

        document.addEventListener('mouseleave', (e) => {
            if (e.target.closest('.settings-dropdown')) {
                this.hideSettingsMenu();
            }
        });
    }

    // Theme Management
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    setTheme(theme) {
        this.currentTheme = theme;
        document.documentElement.setAttribute('data-theme', theme);
        
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        
        if (theme === 'dark') {
            themeIcon.className = 'ph ph-moon text-lg text-indigo-400';
            themeText.textContent = 'Karanlık';
        } else {
            themeIcon.className = 'ph ph-sun text-lg text-yellow-500';
            themeText.textContent = 'Aydınlık';
        }
        
        // Save theme preference
        localStorage.setItem('meschain-theme', theme);
        
        // Trigger theme change event
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme }
        }));
    }

    // Language Management
    setLanguage(language) {
        this.currentLanguage = language;
        
        const flags = {
            'tr': '🇹🇷',
            'en': '🇺🇸',
            'de': '🇩🇪',
            'fr': '🇫🇷'
        };
        
        const names = {
            'tr': 'TR',
            'en': 'EN',
            'de': 'DE',
            'fr': 'FR'
        };
        
        document.getElementById('currentFlag').textContent = flags[language];
        document.getElementById('currentLanguage').textContent = names[language];
        
        // Save language preference
        localStorage.setItem('meschain-language', language);
        
        // Trigger language change event
        window.dispatchEvent(new CustomEvent('languageChanged', {
            detail: { language }
        }));
        
        this.hideLanguageMenu();
    }

    // Dropdown Menu Management
    showLanguageMenu() {
        const menu = document.getElementById('languageMenu');
        if (menu) {
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            menu.style.transform = 'translateY(0)';
        }
    }

    hideLanguageMenu() {
        const menu = document.getElementById('languageMenu');
        if (menu) {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-10px)';
        }
    }

    showNotificationMenu() {
        const menu = document.querySelector('.notification-menu');
        if (menu) {
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            menu.style.transform = 'translateY(0)';
        }
    }

    hideNotificationMenu() {
        const menu = document.querySelector('.notification-menu');
        if (menu) {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-10px)';
        }
    }

    showSettingsMenu() {
        const menu = document.querySelector('.settings-menu');
        if (menu) {
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            menu.style.transform = 'translateY(0)';
        }
    }

    hideSettingsMenu() {
        const menu = document.querySelector('.settings-menu');
        if (menu) {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-10px)';
        }
    }

    // Quick Access Functions
    toggleQuickAccess() {
        const menu = document.getElementById('quickAccessMenu');
        const isVisible = menu.style.opacity === '1';
        
        if (isVisible) {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-10px)';
        } else {
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            menu.style.transform = 'translateY(0)';
        }
    }

    toggleMarketplaceToolbar() {
        const menu = document.getElementById('marketplaceToolbar');
        const isVisible = menu.style.opacity === '1';
        
        if (isVisible) {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-10px)';
        } else {
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            menu.style.transform = 'translateY(0)';
        }
    }

    toggleThemeSelector() {
        const menu = document.getElementById('themeSelector');
        const isVisible = menu.style.opacity === '1';
        
        if (isVisible) {
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-10px)';
        } else {
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            menu.style.transform = 'translateY(0)';
        }
    }

    // Marketplace Functions
    openMarketplace(marketplace) {
        const ports = {
            'trendyol': 3012,
            'amazon': 3011,
            'n11': 3014,
            'hepsiburada': 3010,
            'ebay': 3015,
            'cross-marketplace': 3009
        };

        const port = ports[marketplace];
        if (port) {
            window.open(`http://localhost:${port}`, '_blank');
            this.logActivity(`Opened ${marketplace} marketplace (Port ${port})`);
        }
    }

    openAllMarketplaces() {
        const marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ebay'];
        marketplaces.forEach((marketplace, index) => {
            setTimeout(() => {
                this.openMarketplace(marketplace);
            }, index * 500); // Stagger the opening by 500ms
        });
        this.toggleMarketplaceToolbar();
    }

    showMarketplaceStatus() {
        // This would typically make API calls to check marketplace status
        console.log('Checking marketplace status...');
        this.updateSystemHealth();
    }

    refreshMarketplaces() {
        console.log('Refreshing marketplace connections...');
        // Simulate refresh with visual feedback
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Refreshing...';
        button.style.color = '#3b82f6';
        
        setTimeout(() => {
            button.textContent = originalText;
            button.style.color = '';
            this.logActivity('Marketplace connections refreshed');
        }, 2000);
    }

    // Session Management
    startSessionTimer() {
        let minutes = 29;
        let seconds = 45;
        
        this.sessionTimer = setInterval(() => {
            seconds--;
            
            if (seconds < 0) {
                minutes--;
                seconds = 59;
            }
            
            if (minutes < 0) {
                this.handleSessionExpiry();
                return;
            }
            
            const timerElement = document.getElementById('sessionTimer');
            if (timerElement) {
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                // Change color as time gets low
                if (minutes < 5) {
                    timerElement.parentElement.className = timerElement.parentElement.className.replace('session-normal', 'session-warning');
                }
                if (minutes < 2) {
                    timerElement.parentElement.className = timerElement.parentElement.className.replace('session-warning', 'session-critical');
                }
            }
        }, 1000);
    }

    handleSessionExpiry() {
        clearInterval(this.sessionTimer);
        alert('Session expired. You will be redirected to login.');
        window.location.href = '/login';
    }

    // System Health Management
    updateSystemHealth() {
        const indicator = document.getElementById('system-health-indicator');
        const dot = document.getElementById('health-status-dot');
        const text = document.getElementById('health-status-text');
        
        // Simulate health check
        const healthStatus = Math.random() > 0.1 ? 'healthy' : 'warning';
        
        if (healthStatus === 'healthy') {
            indicator.className = 'flex items-center space-x-1.5 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg px-3 py-1.5 transition-all duration-300';
            dot.className = 'w-2.5 h-2.5 bg-green-500 rounded-full status-indicator-live';
            text.textContent = 'System Healthy';
            text.className = 'text-green-700 dark:text-green-300 font-medium text-xs';
        } else {
            indicator.className = 'flex items-center space-x-1.5 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg px-3 py-1.5 transition-all duration-300';
            dot.className = 'w-2.5 h-2.5 bg-yellow-500 rounded-full animate-pulse';
            text.textContent = 'System Warning';
            text.className = 'text-yellow-700 dark:text-yellow-300 font-medium text-xs';
        }
    }

    // Utility Functions
    logActivity(message) {
        console.log(`[MesChain Header] ${new Date().toISOString()}: ${message}`);
    }

    // Quick Action Functions
    openUsageGuide() {
        window.open('/docs/usage-guide', '_blank');
        this.toggleQuickAccess();
    }

    openTechnicalManual() {
        window.open('/docs/technical-manual', '_blank');
        this.toggleQuickAccess();
    }

    generateSystemReport() {
        console.log('Generating system report...');
        this.toggleQuickAccess();
    }

    openBackupManager() {
        window.open('/admin/backup', '_blank');
        this.toggleQuickAccess();
    }

    // Initialize saved preferences
    loadSavedPreferences() {
        const savedTheme = localStorage.getItem('meschain-theme') || 'light';
        const savedLanguage = localStorage.getItem('meschain-language') || 'tr';
        
        this.setTheme(savedTheme);
        this.setLanguage(savedLanguage);
    }
}

// Global Functions (for onclick attributes)
window.toggleQuickAccess = () => meschainHeader.toggleQuickAccess();
window.toggleMarketplaceToolbar = () => meschainHeader.toggleMarketplaceToolbar();
window.toggleThemeSelector = () => meschainHeader.toggleThemeSelector();
window.setTheme = (theme) => meschainHeader.setTheme(theme);
window.setLanguage = (language) => meschainHeader.setLanguage(language);
window.openMarketplace = (marketplace) => meschainHeader.openMarketplace(marketplace);
window.openAllMarketplaces = () => meschainHeader.openAllMarketplaces();
window.showMarketplaceStatus = () => meschainHeader.showMarketplaceStatus();
window.refreshMarketplaces = () => meschainHeader.refreshMarketplaces();
window.openUsageGuide = () => meschainHeader.openUsageGuide();
window.openTechnicalManual = () => meschainHeader.openTechnicalManual();
window.generateSystemReport = () => meschainHeader.generateSystemReport();
window.openBackupManager = () => meschainHeader.openBackupManager();

// Initialize header when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.meschainHeader = new MeschainHeader();
    window.meschainHeader.loadSavedPreferences();
});

// Update system health every 30 seconds
setInterval(() => {
    if (window.meschainHeader) {
        window.meschainHeader.updateSystemHealth();
    }
}, 30000);
