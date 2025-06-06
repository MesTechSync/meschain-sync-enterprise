/**
 * MesChain Enterprise - Theme Switcher JavaScript
 * Selinay Takımı - Multi-Tema Yönetim Sistemi
 * Haziran 6, 2025
 */

class MesChainThemeManager {
    constructor() {
        this.currentTheme = 'professional';
        this.availableThemes = {
            'professional': {
                name: 'Professional',
                description: 'Corporate ve profesyonel görünüm',
                icon: '💼',
                colors: {
                    primary: '#2563eb',
                    secondary: '#64748b',
                    accent: '#0ea5e9'
                }
            },
            'modern': {
                name: 'Modern',
                description: 'Minimal ve çağdaş tasarım',
                icon: '✨',
                colors: {
                    primary: '#8b5cf6',
                    secondary: '#a855f7',
                    accent: '#ec4899'
                }
            },
            'dark': {
                name: 'Dark Mode',
                description: 'Koyu tema, göz dostu',
                icon: '🌙',
                colors: {
                    primary: '#3b82f6',
                    secondary: '#6366f1',
                    accent: '#06b6d4'
                }
            },
            'colorful': {
                name: 'Colorful',
                description: 'Canlı ve enerjik renkler',
                icon: '🎨',
                colors: {
                    primary: '#f59e0b',
                    secondary: '#ef4444',
                    accent: '#10b981'
                }
            }
        };
        
        this.init();
    }

    init() {
        this.loadSavedTheme();
        this.createThemeSwitcher();
        this.bindEvents();
        this.applyTheme(this.currentTheme);
        
        console.log('🎨 MesChain Theme Manager başlatıldı!');
    }

    loadSavedTheme() {
        const savedTheme = localStorage.getItem('meschain-theme');
        if (savedTheme && this.availableThemes[savedTheme]) {
            this.currentTheme = savedTheme;
        }
    }

    createThemeSwitcher() {
        // Tema değiştirici kontrol paneli oluştur
        const switcherContainer = document.createElement('div');
        switcherContainer.className = 'theme-switcher-container';
        switcherContainer.innerHTML = `
            <div class="theme-switcher">
                <button class="theme-toggle-btn" id="themeToggleBtn">
                    <span class="theme-icon">${this.availableThemes[this.currentTheme].icon}</span>
                    <span class="theme-name">${this.availableThemes[this.currentTheme].name}</span>
                </button>
                
                <div class="theme-dropdown" id="themeDropdown">
                    <div class="theme-dropdown-header">
                        <h4>🎨 Tema Seçin</h4>
                    </div>
                    <div class="theme-options">
                        ${Object.entries(this.availableThemes).map(([key, theme]) => `
                            <div class="theme-option ${key === this.currentTheme ? 'active' : ''}" 
                                 data-theme="${key}">
                                <span class="theme-option-icon">${theme.icon}</span>
                                <div class="theme-option-content">
                                    <div class="theme-option-name">${theme.name}</div>
                                    <div class="theme-option-desc">${theme.description}</div>
                                </div>
                                <div class="theme-option-colors">
                                    <span class="color-preview" style="background-color: ${theme.colors.primary}"></span>
                                    <span class="color-preview" style="background-color: ${theme.colors.secondary}"></span>
                                    <span class="color-preview" style="background-color: ${theme.colors.accent}"></span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="theme-dropdown-footer">
                        <small>Tema tercihiniz otomatik kaydedilir</small>
                    </div>
                </div>
            </div>
        `;

        // Sayfa başına tema değiştiricisini ekle
        document.body.appendChild(switcherContainer);
        
        // CSS stilleri ekle
        this.addThemeSwitcherStyles();
    }

    addThemeSwitcherStyles() {
        const styles = `
            <style>
                .theme-switcher-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    font-family: var(--font-family-primary);
                }

                .theme-switcher {
                    position: relative;
                }

                .theme-toggle-btn {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 16px;
                    background: var(--surface);
                    border: 2px solid var(--primary);
                    border-radius: 12px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    color: var(--text);
                    box-shadow: var(--shadow-md);
                    transition: all 0.3s ease;
                }

                .theme-toggle-btn:hover {
                    background: var(--primary);
                    color: white;
                    transform: translateY(-2px);
                    box-shadow: var(--shadow-lg);
                }

                .theme-dropdown {
                    position: absolute;
                    top: 60px;
                    right: 0;
                    min-width: 320px;
                    background: var(--surface);
                    border: 1px solid rgba(0, 0, 0, 0.1);
                    border-radius: 16px;
                    box-shadow: var(--shadow-xl);
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(-10px);
                    transition: all 0.3s ease;
                    backdrop-filter: blur(10px);
                }

                .theme-dropdown.show {
                    opacity: 1;
                    visibility: visible;
                    transform: translateY(0);
                }

                .theme-dropdown-header {
                    padding: 16px 20px;
                    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                    text-align: center;
                }

                .theme-dropdown-header h4 {
                    margin: 0;
                    color: var(--text);
                    font-size: 16px;
                }

                .theme-options {
                    padding: 12px;
                }

                .theme-option {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 12px;
                    border-radius: 12px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    margin-bottom: 8px;
                }

                .theme-option:hover {
                    background: var(--background);
                    transform: translateX(4px);
                }

                .theme-option.active {
                    background: var(--primary);
                    color: white;
                }

                .theme-option-icon {
                    font-size: 20px;
                    min-width: 20px;
                }

                .theme-option-content {
                    flex: 1;
                }

                .theme-option-name {
                    font-weight: 600;
                    font-size: 14px;
                    margin-bottom: 2px;
                }

                .theme-option-desc {
                    font-size: 12px;
                    opacity: 0.7;
                }

                .theme-option-colors {
                    display: flex;
                    gap: 4px;
                }

                .color-preview {
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    border: 2px solid rgba(255, 255, 255, 0.5);
                }

                .theme-dropdown-footer {
                    padding: 12px 20px;
                    border-top: 1px solid rgba(0, 0, 0, 0.05);
                    text-align: center;
                }

                .theme-dropdown-footer small {
                    color: var(--text);
                    opacity: 0.6;
                }

                /* Mobile responsive */
                @media (max-width: 768px) {
                    .theme-switcher-container {
                        top: 10px;
                        right: 10px;
                    }

                    .theme-dropdown {
                        min-width: 280px;
                        right: -10px;
                    }

                    .theme-toggle-btn {
                        padding: 10px 12px;
                        font-size: 13px;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    bindEvents() {
        // Tema toggle butonu
        document.addEventListener('click', (e) => {
            if (e.target.closest('#themeToggleBtn')) {
                this.toggleThemeDropdown();
            }
            
            // Tema seçimi
            const themeOption = e.target.closest('.theme-option');
            if (themeOption) {
                const selectedTheme = themeOption.dataset.theme;
                this.changeTheme(selectedTheme);
            }
            
            // Dropdown dışına tıklama - kapat
            if (!e.target.closest('.theme-switcher')) {
                this.closeThemeDropdown();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeThemeDropdown();
            }
        });

        // System theme change detection
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (e.matches && this.currentTheme !== 'dark') {
                this.changeTheme('dark');
            }
        });
    }

    toggleThemeDropdown() {
        const dropdown = document.getElementById('themeDropdown');
        dropdown.classList.toggle('show');
    }

    closeThemeDropdown() {
        const dropdown = document.getElementById('themeDropdown');
        dropdown.classList.remove('show');
    }

    changeTheme(themeName) {
        if (!this.availableThemes[themeName]) {
            console.error(`Tema bulunamadı: ${themeName}`);
            return;
        }

        this.currentTheme = themeName;
        this.applyTheme(themeName);
        this.saveTheme(themeName);
        this.updateUI();
        this.closeThemeDropdown();

        // Tema değişikliği eventi
        this.dispatchThemeChangeEvent(themeName);
        
        console.log(`🎨 Tema değiştirildi: ${this.availableThemes[themeName].name}`);
    }

    applyTheme(themeName) {
        // HTML elementine tema attribute'u ekle
        document.documentElement.setAttribute('data-theme', themeName);
        
        // Body'ye tema class'ı ekle
        document.body.className = document.body.className.replace(/theme-\w+/g, '');
        document.body.classList.add(`theme-${themeName}`);

        // Meta theme-color güncelle (mobile)
        this.updateMetaThemeColor(themeName);

        // Smooth transition effect
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    }

    updateMetaThemeColor(themeName) {
        const theme = this.availableThemes[themeName];
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        
        metaThemeColor.content = theme.colors.primary;
    }

    updateUI() {
        const toggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = toggleBtn.querySelector('.theme-icon');
        const themeName = toggleBtn.querySelector('.theme-name');
        
        const currentThemeData = this.availableThemes[this.currentTheme];
        themeIcon.textContent = currentThemeData.icon;
        themeName.textContent = currentThemeData.name;

        // Active tema option'ı güncelle
        document.querySelectorAll('.theme-option').forEach(option => {
            option.classList.remove('active');
            if (option.dataset.theme === this.currentTheme) {
                option.classList.add('active');
            }
        });
    }

    saveTheme(themeName) {
        localStorage.setItem('meschain-theme', themeName);
    }

    dispatchThemeChangeEvent(themeName) {
        const event = new CustomEvent('themeChanged', {
            detail: {
                theme: themeName,
                themeData: this.availableThemes[themeName]
            }
        });
        window.dispatchEvent(event);
    }

    // Public API
    getCurrentTheme() {
        return this.currentTheme;
    }

    getAvailableThemes() {
        return this.availableThemes;
    }

    setTheme(themeName) {
        this.changeTheme(themeName);
    }

    // OpenCart Integration
    integrateWithOpenCart() {
        // OpenCart admin panel tema entegrasyonu
        if (window.$ && $('.nav-tabs').length > 0) {
            this.addOpenCartThemeSupport();
        }
    }

    addOpenCartThemeSupport() {
        // OpenCart form elementlerini tema sistemine entegre et
        $('input, select, textarea').addClass('form-input');
        $('button, .btn').addClass('btn');
        $('.panel, .box').addClass('card');
        
        console.log('🔧 OpenCart tema entegrasyonu tamamlandı');
    }
}

// Auto-initialize theme manager
document.addEventListener('DOMContentLoaded', function() {
    window.mesChainThemes = new MesChainThemeManager();
    
    // OpenCart detection and integration
    if (typeof $ !== 'undefined') {
        window.mesChainThemes.integrateWithOpenCart();
    }
});

// Theme change listener example
window.addEventListener('themeChanged', function(e) {
    console.log('Tema değişti:', e.detail.theme, e.detail.themeData);
    
    // Custom integrations burada yapılabilir
    // Örnek: Charts'ları yeni tema renklerine göre güncelle
    if (window.Chart) {
        // Chart.js tema güncellemesi
        Chart.defaults.color = getComputedStyle(document.documentElement)
            .getPropertyValue('--text').trim();
    }
});

// Export for external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MesChainThemeManager;
}
