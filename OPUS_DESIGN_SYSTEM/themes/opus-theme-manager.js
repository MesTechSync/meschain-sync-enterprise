/**
 * OPUS DESIGN SYSTEM - Advanced Theme Manager
 * Dynamic theme switching with smooth transitions
 * 
 * Features:
 * - Dark/Light theme toggle
 * - Custom theme creation
 * - System preference detection
 * - Local storage persistence
 * - Smooth transitions
 * - Real-time preview
 */

class OpusThemeManager {
  constructor() {
    this.themes = {
      light: {
        name: 'Light',
        icon: '☀️',
        colors: {
          '--opus-bg-primary': '#ffffff',
          '--opus-bg-secondary': '#fafafa',
          '--opus-bg-tertiary': '#f4f4f5',
          '--opus-bg-elevated': '#ffffff',
          '--opus-text-primary': '#18181b',
          '--opus-text-secondary': '#3f3f46',
          '--opus-text-tertiary': '#71717a',
          '--opus-text-disabled': '#a1a1aa',
          '--opus-border-primary': '#e4e4e7',
          '--opus-border-secondary': '#d4d4d8',
          '--opus-border-tertiary': '#a1a1aa'
        }
      },
      dark: {
        name: 'Dark',
        icon: '🌙',
        colors: {
          '--opus-bg-primary': '#09090b',
          '--opus-bg-secondary': '#18181b',
          '--opus-bg-tertiary': '#27272a',
          '--opus-bg-elevated': '#27272a',
          '--opus-text-primary': '#fafafa',
          '--opus-text-secondary': '#d4d4d8',
          '--opus-text-tertiary': '#a1a1aa',
          '--opus-text-disabled': '#52525b',
          '--opus-border-primary': '#27272a',
          '--opus-border-secondary': '#3f3f46',
          '--opus-border-tertiary': '#52525b'
        }
      },
      midnight: {
        name: 'Midnight',
        icon: '🌌',
        colors: {
          '--opus-bg-primary': '#000814',
          '--opus-bg-secondary': '#001d3d',
          '--opus-bg-tertiary': '#003566',
          '--opus-bg-elevated': '#003566',
          '--opus-text-primary': '#ffd60a',
          '--opus-text-secondary': '#ffc300',
          '--opus-text-tertiary': '#ffb700',
          '--opus-text-disabled': '#666666',
          '--opus-border-primary': '#003566',
          '--opus-border-secondary': '#004080',
          '--opus-border-tertiary': '#0066cc'
        }
      },
      sunset: {
        name: 'Sunset',
        icon: '🌅',
        colors: {
          '--opus-bg-primary': '#fff5f5',
          '--opus-bg-secondary': '#fed7d7',
          '--opus-bg-tertiary': '#feb2b2',
          '--opus-bg-elevated': '#ffffff',
          '--opus-text-primary': '#742a2a',
          '--opus-text-secondary': '#9b2c2c',
          '--opus-text-tertiary': '#c53030',
          '--opus-text-disabled': '#fc8181',
          '--opus-border-primary': '#feb2b2',
          '--opus-border-secondary': '#fc8181',
          '--opus-border-tertiary': '#f56565'
        }
      },
      ocean: {
        name: 'Ocean',
        icon: '🌊',
        colors: {
          '--opus-bg-primary': '#e0f2fe',
          '--opus-bg-secondary': '#bae6fd',
          '--opus-bg-tertiary': '#7dd3fc',
          '--opus-bg-elevated': '#ffffff',
          '--opus-text-primary': '#075985',
          '--opus-text-secondary': '#0369a1',
          '--opus-text-tertiary': '#0284c7',
          '--opus-text-disabled': '#38bdf8',
          '--opus-border-primary': '#7dd3fc',
          '--opus-border-secondary': '#38bdf8',
          '--opus-border-tertiary': '#0ea5e9'
        }
      },
      forest: {
        name: 'Forest',
        icon: '🌲',
        colors: {
          '--opus-bg-primary': '#f0fdf4',
          '--opus-bg-secondary': '#dcfce7',
          '--opus-bg-tertiary': '#bbf7d0',
          '--opus-bg-elevated': '#ffffff',
          '--opus-text-primary': '#14532d',
          '--opus-text-secondary': '#166534',
          '--opus-text-tertiary': '#15803d',
          '--opus-text-disabled': '#22c55e',
          '--opus-border-primary': '#bbf7d0',
          '--opus-border-secondary': '#86efac',
          '--opus-border-tertiary': '#4ade80'
        }
      }
    };

    this.currentTheme = 'light';
    this.customThemes = {};
    this.init();
  }

  init() {
    // Load saved theme preference
    const savedTheme = localStorage.getItem('opus-theme');
    const savedCustomThemes = localStorage.getItem('opus-custom-themes');
    
    if (savedCustomThemes) {
      this.customThemes = JSON.parse(savedCustomThemes);
    }
    
    // Check system preference
    if (!savedTheme && window.matchMedia) {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      this.currentTheme = prefersDark ? 'dark' : 'light';
    } else if (savedTheme) {
      this.currentTheme = savedTheme;
    }
    
    // Apply initial theme
    this.applyTheme(this.currentTheme);
    
    // Listen for system theme changes
    if (window.matchMedia) {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (localStorage.getItem('opus-theme-auto') === 'true') {
          this.applyTheme(e.matches ? 'dark' : 'light');
        }
      });
    }
  }

  applyTheme(themeName) {
    const theme = this.themes[themeName] || this.customThemes[themeName];
    
    if (!theme) {
      console.error(`Theme "${themeName}" not found`);
      return;
    }
    
    // Add transition class
    document.documentElement.classList.add('opus-theme-transitioning');
    
    // Apply theme colors
    Object.entries(theme.colors).forEach(([property, value]) => {
      document.documentElement.style.setProperty(property, value);
    });
    
    // Update data attribute
    document.documentElement.setAttribute('data-theme', themeName);
    
    // Save preference
    this.currentTheme = themeName;
    localStorage.setItem('opus-theme', themeName);
    
    // Remove transition class after animation
    setTimeout(() => {
      document.documentElement.classList.remove('opus-theme-transitioning');
    }, 300);
    
    // Dispatch theme change event
    window.dispatchEvent(new CustomEvent('opus-theme-changed', {
      detail: { theme: themeName, colors: theme.colors }
    }));
  }

  toggleTheme() {
    const themes = Object.keys(this.themes);
    const currentIndex = themes.indexOf(this.currentTheme);
    const nextIndex = (currentIndex + 1) % themes.length;
    this.applyTheme(themes[nextIndex]);
  }

  createCustomTheme(name, colors) {
    const customTheme = {
      name,
      icon: '🎨',
      colors: { ...this.themes.light.colors, ...colors }
    };
    
    this.customThemes[name] = customTheme;
    localStorage.setItem('opus-custom-themes', JSON.stringify(this.customThemes));
    
    return customTheme;
  }

  deleteCustomTheme(name) {
    delete this.customThemes[name];
    localStorage.setItem('opus-custom-themes', JSON.stringify(this.customThemes));
    
    // If current theme is deleted, switch to light
    if (this.currentTheme === name) {
      this.applyTheme('light');
    }
  }

  getAllThemes() {
    return { ...this.themes, ...this.customThemes };
  }

  getCurrentTheme() {
    return this.currentTheme;
  }

  getThemeColors(themeName) {
    const theme = this.themes[themeName] || this.customThemes[themeName];
    return theme ? theme.colors : null;
  }

  exportTheme(themeName) {
    const theme = this.themes[themeName] || this.customThemes[themeName];
    if (!theme) return null;
    
    return {
      name: theme.name,
      colors: theme.colors,
      exportDate: new Date().toISOString()
    };
  }

  importTheme(themeData) {
    if (!themeData.name || !themeData.colors) {
      throw new Error('Invalid theme data');
    }
    
    const importedName = `imported-${themeData.name}-${Date.now()}`;
    return this.createCustomTheme(importedName, themeData.colors);
  }

  // Create theme switcher UI component
  createThemeSwitcher() {
    const switcher = document.createElement('div');
    switcher.className = 'opus-theme-switcher';
    switcher.innerHTML = `
      <button class="opus-theme-switcher-toggle opus-btn opus-btn-ghost">
        <span class="opus-theme-icon">${this.themes[this.currentTheme].icon}</span>
        <span class="opus-theme-name">${this.themes[this.currentTheme].name}</span>
      </button>
      <div class="opus-theme-switcher-dropdown opus-dropdown-menu">
        ${Object.entries(this.getAllThemes()).map(([key, theme]) => `
          <button class="opus-theme-option ${key === this.currentTheme ? 'active' : ''}" 
                  data-theme="${key}">
            <span class="opus-theme-icon">${theme.icon}</span>
            <span class="opus-theme-name">${theme.name}</span>
          </button>
        `).join('')}
        <div class="opus-dropdown-divider"></div>
        <button class="opus-theme-custom-btn">
          <span class="opus-theme-icon">🎨</span>
          <span class="opus-theme-name">Create Custom Theme</span>
        </button>
      </div>
    `;
    
    // Add event listeners
    const toggle = switcher.querySelector('.opus-theme-switcher-toggle');
    const dropdown = switcher.querySelector('.opus-theme-switcher-dropdown');
    
    toggle.addEventListener('click', () => {
      switcher.classList.toggle('active');
    });
    
    switcher.querySelectorAll('.opus-theme-option').forEach(option => {
      option.addEventListener('click', () => {
        const themeName = option.dataset.theme;
        this.applyTheme(themeName);
        switcher.classList.remove('active');
        
        // Update UI
        toggle.querySelector('.opus-theme-icon').textContent = this.themes[themeName].icon;
        toggle.querySelector('.opus-theme-name').textContent = this.themes[themeName].name;
        
        // Update active state
        switcher.querySelectorAll('.opus-theme-option').forEach(opt => {
          opt.classList.toggle('active', opt.dataset.theme === themeName);
        });
      });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!switcher.contains(e.target)) {
        switcher.classList.remove('active');
      }
    });
    
    return switcher;
  }
}

// Initialize theme manager
const opusTheme = new OpusThemeManager();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = OpusThemeManager;
}

// Add CSS for theme transitions
const style = document.createElement('style');
style.textContent = `
  .opus-theme-transitioning,
  .opus-theme-transitioning *,
  .opus-theme-transitioning *::before,
  .opus-theme-transitioning *::after {
    transition: background-color 300ms ease-in-out,
                color 300ms ease-in-out,
                border-color 300ms ease-in-out,
                box-shadow 300ms ease-in-out !important;
  }
  
  .opus-theme-switcher {
    position: relative;
    display: inline-block;
  }
  
  .opus-theme-switcher-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .opus-theme-switcher-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    min-width: 200px;
    max-height: 400px;
    overflow-y: auto;
  }
  
  .opus-theme-option,
  .opus-theme-custom-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 16px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 200ms;
    text-align: left;
  }
  
  .opus-theme-option:hover,
  .opus-theme-custom-btn:hover {
    background: var(--opus-bg-secondary);
  }
  
  .opus-theme-option.active {
    color: var(--opus-primary-500);
    background: var(--opus-primary-50);
  }
  
  .opus-theme-icon {
    font-size: 20px;
  }
`;
document.head.appendChild(style); 