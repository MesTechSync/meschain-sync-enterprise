/**
 * 🎨 SELINAY TASK 9 PHASE 1 - MICROSOFT 365 DESIGN SYSTEM
 * Enterprise-Grade Microsoft 365 Design System Implementation
 * 
 * FEATURES:
 * ✅ Microsoft 365 color palette with semantic tokens
 * ✅ Fluent Design typography system
 * ✅ Enterprise component library
 * ✅ Dark mode and accessibility support
 * ✅ Responsive design tokens
 * 
 * @author Selinay - Frontend UI/UX Specialist
 * @version 1.0.0 - Task 9 Microsoft 365 Excellence
 * @date June 7, 2025
 */

class Microsoft365ThemeSystem {
    constructor() {
        this.version = "1.0.0";
        this.themeName = "Microsoft365Enterprise";
        this.initializeThemeSystem();
        this.setupResponsiveBreakpoints();
        this.initializeColorTokens();
        this.setupTypographySystem();
        this.createComponentLibrary();
        
        console.log('🎨 Microsoft 365 Design System initialized');
    }

    /**
     * 🎨 Initialize Theme System
     */
    initializeThemeSystem() {
        this.themeConfig = {
            // Microsoft 365 Brand Colors
            brand: {
                primary: '#2563eb',      // Microsoft Blue
                success: '#059669',      // Green
                danger: '#dc2626',       // Red
                warning: '#d97706',      // Orange
                info: '#0ea5e9',         // Light Blue
                secondary: '#6b7280'     // Gray
            },
            
            // Semantic Color System
            semantic: {
                text: {
                    primary: '#111827',
                    secondary: '#6b7280',
                    tertiary: '#9ca3af',
                    inverse: '#ffffff',
                    link: '#2563eb',
                    success: '#059669',
                    warning: '#d97706',
                    danger: '#dc2626'
                },
                background: {
                    primary: '#ffffff',
                    secondary: '#f9fafb',
                    tertiary: '#f3f4f6',
                    inverse: '#111827',
                    overlay: 'rgba(0, 0, 0, 0.5)',
                    card: '#ffffff',
                    surface: '#f8fafc'
                },
                border: {
                    primary: '#e5e7eb',
                    secondary: '#d1d5db',
                    focus: '#2563eb',
                    danger: '#dc2626',
                    success: '#059669'
                }
            },
            
            // Dark Mode Support
            darkMode: {
                text: {
                    primary: '#f9fafb',
                    secondary: '#d1d5db',
                    tertiary: '#9ca3af'
                },
                background: {
                    primary: '#111827',
                    secondary: '#1f2937',
                    tertiary: '#374151',
                    card: '#1f2937',
                    surface: '#111827'
                },
                border: {
                    primary: '#374151',
                    secondary: '#4b5563',
                    focus: '#3b82f6'
                }
            }
        };
    }

    /**
     * 📱 Setup Responsive Breakpoints
     */
    setupResponsiveBreakpoints() {
        this.breakpoints = {
            xs: '320px',
            sm: '640px',
            md: '768px',
            lg: '1024px',
            xl: '1280px',
            '2xl': '1536px'
        };
        
        this.spacing = {
            '0': '0px',
            '1': '0.25rem',   // 4px
            '2': '0.5rem',    // 8px
            '3': '0.75rem',   // 12px
            '4': '1rem',      // 16px
            '5': '1.25rem',   // 20px
            '6': '1.5rem',    // 24px
            '8': '2rem',      // 32px
            '10': '2.5rem',   // 40px
            '12': '3rem',     // 48px
            '16': '4rem',     // 64px
            '20': '5rem',     // 80px
            '24': '6rem'      // 96px
        };
    }

    /**
     * 🎨 Initialize Color Tokens
     */
    initializeColorTokens() {
        this.colorTokens = {
            // Microsoft 365 Primary Palette
            blue: {
                50: '#eff6ff',
                100: '#dbeafe',
                200: '#bfdbfe',
                300: '#93c5fd',
                400: '#60a5fa',
                500: '#2563eb',   // Primary Blue
                600: '#1d4ed8',
                700: '#1e40af',
                800: '#1e3a8a',
                900: '#1e3a8a'
            },
            
            green: {
                50: '#ecfdf5',
                100: '#d1fae5',
                200: '#a7f3d0',
                300: '#6ee7b7',
                400: '#34d399',
                500: '#059669',   // Success Green
                600: '#047857',
                700: '#065f46',
                800: '#064e3b',
                900: '#022c22'
            },
            
            red: {
                50: '#fef2f2',
                100: '#fee2e2',
                200: '#fecaca',
                300: '#fca5a5',
                400: '#f87171',
                500: '#dc2626',   // Danger Red
                600: '#b91c1c',
                700: '#991b1b',
                800: '#7f1d1d',
                900: '#7f1d1d'
            },
            
            gray: {
                50: '#f9fafb',
                100: '#f3f4f6',
                200: '#e5e7eb',
                300: '#d1d5db',
                400: '#9ca3af',
                500: '#6b7280',
                600: '#4b5563',
                700: '#374151',
                800: '#1f2937',
                900: '#111827'
            }
        };
    }

    /**
     * 📝 Setup Typography System
     */
    setupTypographySystem() {
        this.typography = {
            fontFamily: {
                sans: ['Segoe UI', 'system-ui', '-apple-system', 'sans-serif'],
                serif: ['ui-serif', 'Georgia', 'serif'],
                mono: ['ui-monospace', 'SFMono-Regular', 'Consolas', 'monospace']
            },
            
            fontSize: {
                xs: ['0.75rem', { lineHeight: '1rem' }],      // 12px
                sm: ['0.875rem', { lineHeight: '1.25rem' }],  // 14px
                base: ['1rem', { lineHeight: '1.5rem' }],     // 16px
                lg: ['1.125rem', { lineHeight: '1.75rem' }],  // 18px
                xl: ['1.25rem', { lineHeight: '1.75rem' }],   // 20px
                '2xl': ['1.5rem', { lineHeight: '2rem' }],    // 24px
                '3xl': ['1.875rem', { lineHeight: '2.25rem' }], // 30px
                '4xl': ['2.25rem', { lineHeight: '2.5rem' }], // 36px
                '5xl': ['3rem', { lineHeight: '1' }],         // 48px
                '6xl': ['3.75rem', { lineHeight: '1' }]       // 60px
            },
            
            fontWeight: {
                thin: '100',
                extralight: '200',
                light: '300',
                normal: '400',
                medium: '500',
                semibold: '600',
                bold: '700',
                extrabold: '800',
                black: '900'
            },
            
            // Microsoft 365 Typography Styles
            styles: {
                heading1: {
                    fontSize: '2.25rem',
                    fontWeight: '600',
                    lineHeight: '2.5rem',
                    letterSpacing: '-0.025em'
                },
                heading2: {
                    fontSize: '1.875rem',
                    fontWeight: '600',
                    lineHeight: '2.25rem',
                    letterSpacing: '-0.025em'
                },
                heading3: {
                    fontSize: '1.5rem',
                    fontWeight: '600',
                    lineHeight: '2rem'
                },
                body: {
                    fontSize: '1rem',
                    fontWeight: '400',
                    lineHeight: '1.5rem'
                },
                caption: {
                    fontSize: '0.875rem',
                    fontWeight: '400',
                    lineHeight: '1.25rem',
                    color: '#6b7280'
                },
                small: {
                    fontSize: '0.75rem',
                    fontWeight: '400',
                    lineHeight: '1rem',
                    color: '#9ca3af'
                }
            }
        };
    }

    /**
     * 🧩 Create Component Library
     */
    createComponentLibrary() {
        this.components = {
            button: this.createButtonStyles(),
            card: this.createCardStyles(),
            input: this.createInputStyles(),
            nav: this.createNavigationStyles(),
            dashboard: this.createDashboardStyles()
        };
    }

    /**
     * 🔘 Create Button Styles
     */
    createButtonStyles() {
        return {
            base: {
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                borderRadius: '6px',
                fontSize: '0.875rem',
                fontWeight: '500',
                transition: 'all 0.2s ease-in-out',
                cursor: 'pointer',
                border: 'none',
                outline: 'none',
                textDecoration: 'none'
            },
            
            sizes: {
                sm: {
                    padding: '6px 12px',
                    fontSize: '0.75rem',
                    minHeight: '32px'
                },
                md: {
                    padding: '8px 16px',
                    fontSize: '0.875rem',
                    minHeight: '36px'
                },
                lg: {
                    padding: '12px 24px',
                    fontSize: '1rem',
                    minHeight: '44px'
                }
            },
            
            variants: {
                primary: {
                    backgroundColor: '#2563eb',
                    color: '#ffffff',
                    '&:hover': {
                        backgroundColor: '#1d4ed8'
                    },
                    '&:focus': {
                        boxShadow: '0 0 0 3px rgba(37, 99, 235, 0.3)'
                    }
                },
                success: {
                    backgroundColor: '#059669',
                    color: '#ffffff',
                    '&:hover': {
                        backgroundColor: '#047857'
                    }
                },
                danger: {
                    backgroundColor: '#dc2626',
                    color: '#ffffff',
                    '&:hover': {
                        backgroundColor: '#b91c1c'
                    }
                },
                outline: {
                    backgroundColor: 'transparent',
                    color: '#2563eb',
                    border: '1px solid #2563eb',
                    '&:hover': {
                        backgroundColor: '#2563eb',
                        color: '#ffffff'
                    }
                },
                ghost: {
                    backgroundColor: 'transparent',
                    color: '#6b7280',
                    '&:hover': {
                        backgroundColor: '#f3f4f6',
                        color: '#374151'
                    }
                }
            }
        };
    }

    /**
     * 🃏 Create Card Styles
     */
    createCardStyles() {
        return {
            base: {
                backgroundColor: '#ffffff',
                borderRadius: '8px',
                boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
                border: '1px solid #e5e7eb',
                overflow: 'hidden'
            },
            
            variants: {
                default: {
                    padding: '24px'
                },
                compact: {
                    padding: '16px'
                },
                spacious: {
                    padding: '32px'
                }
            },
            
            header: {
                borderBottom: '1px solid #e5e7eb',
                marginBottom: '16px',
                paddingBottom: '16px'
            },
            
            title: {
                fontSize: '1.125rem',
                fontWeight: '600',
                color: '#111827',
                margin: '0 0 4px 0'
            },
            
            subtitle: {
                fontSize: '0.875rem',
                color: '#6b7280',
                margin: '0'
            },
            
            content: {
                color: '#374151',
                lineHeight: '1.6'
            }
        };
    }

    /**
     * 📝 Create Input Styles
     */
    createInputStyles() {
        return {
            base: {
                width: '100%',
                padding: '8px 12px',
                fontSize: '0.875rem',
                lineHeight: '1.5',
                color: '#111827',
                backgroundColor: '#ffffff',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                transition: 'border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out',
                '&:focus': {
                    outline: 'none',
                    borderColor: '#2563eb',
                    boxShadow: '0 0 0 3px rgba(37, 99, 235, 0.1)'
                },
                '&::placeholder': {
                    color: '#9ca3af'
                }
            },
            
            sizes: {
                sm: {
                    padding: '6px 10px',
                    fontSize: '0.75rem'
                },
                md: {
                    padding: '8px 12px',
                    fontSize: '0.875rem'
                },
                lg: {
                    padding: '12px 16px',
                    fontSize: '1rem'
                }
            },
            
            states: {
                error: {
                    borderColor: '#dc2626',
                    '&:focus': {
                        borderColor: '#dc2626',
                        boxShadow: '0 0 0 3px rgba(220, 38, 38, 0.1)'
                    }
                },
                success: {
                    borderColor: '#059669',
                    '&:focus': {
                        borderColor: '#059669',
                        boxShadow: '0 0 0 3px rgba(5, 150, 105, 0.1)'
                    }
                }
            }
        };
    }

    /**
     * 🧭 Create Navigation Styles
     */
    createNavigationStyles() {
        return {
            navbar: {
                backgroundColor: '#ffffff',
                borderBottom: '1px solid #e5e7eb',
                padding: '12px 0',
                position: 'sticky',
                top: '0',
                zIndex: '50'
            },
            
            navItem: {
                padding: '8px 16px',
                color: '#6b7280',
                textDecoration: 'none',
                borderRadius: '6px',
                transition: 'all 0.2s ease-in-out',
                fontSize: '0.875rem',
                fontWeight: '500',
                '&:hover': {
                    color: '#2563eb',
                    backgroundColor: '#f3f4f6'
                },
                '&.active': {
                    color: '#2563eb',
                    backgroundColor: '#eff6ff'
                }
            },
            
            sidebar: {
                width: '256px',
                backgroundColor: '#ffffff',
                borderRight: '1px solid #e5e7eb',
                padding: '24px 0',
                height: '100vh',
                position: 'fixed',
                overflowY: 'auto'
            }
        };
    }

    /**
     * 📊 Create Dashboard Styles
     */
    createDashboardStyles() {
        return {
            container: {
                maxWidth: '1280px',
                margin: '0 auto',
                padding: '0 16px'
            },
            
            grid: {
                display: 'grid',
                gap: '24px',
                gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))'
            },
            
            header: {
                marginBottom: '32px',
                borderBottom: '1px solid #e5e7eb',
                paddingBottom: '16px'
            },
            
            stat: {
                backgroundColor: '#ffffff',
                padding: '24px',
                borderRadius: '8px',
                border: '1px solid #e5e7eb',
                textAlign: 'center'
            },
            
            statValue: {
                fontSize: '2rem',
                fontWeight: '700',
                color: '#111827',
                marginBottom: '4px'
            },
            
            statLabel: {
                fontSize: '0.875rem',
                color: '#6b7280',
                fontWeight: '500'
            }
        };
    }

    /**
     * 🌙 Toggle Dark Mode
     */
    toggleDarkMode() {
        const isDark = document.documentElement.classList.contains('dark');
        
        if (isDark) {
            document.documentElement.classList.remove('dark');
            this.applyTheme('light');
        } else {
            document.documentElement.classList.add('dark');
            this.applyTheme('dark');
        }
        
        console.log(`🌙 Theme switched to ${isDark ? 'light' : 'dark'} mode`);
    }

    /**
     * 🎨 Apply Theme
     */
    applyTheme(mode = 'light') {
        const root = document.documentElement;
        const theme = mode === 'dark' ? this.themeConfig.darkMode : this.themeConfig.semantic;
        
        // Apply CSS custom properties
        Object.entries(theme).forEach(([category, values]) => {
            Object.entries(values).forEach(([key, value]) => {
                root.style.setProperty(`--${category}-${key}`, value);
            });
        });
        
        // Apply brand colors
        Object.entries(this.themeConfig.brand).forEach(([key, value]) => {
            root.style.setProperty(`--color-${key}`, value);
        });
    }

    /**
     * 📱 Generate Responsive CSS
     */
    generateResponsiveCSS() {
        let css = `
/* Microsoft 365 Design System - Generated CSS */
:root {
    /* Typography */
    --font-sans: ${this.typography.fontFamily.sans.join(', ')};
    --font-serif: ${this.typography.fontFamily.serif.join(', ')};
    --font-mono: ${this.typography.fontFamily.mono.join(', ')};
}

/* Responsive breakpoints */
@media (min-width: ${this.breakpoints.sm}) { .sm\\:block { display: block; } }
@media (min-width: ${this.breakpoints.md}) { .md\\:block { display: block; } }
@media (min-width: ${this.breakpoints.lg}) { .lg\\:block { display: block; } }
@media (min-width: ${this.breakpoints.xl}) { .xl\\:block { display: block; } }

/* Utility classes */
.text-xs { font-size: ${this.typography.fontSize.xs[0]}; line-height: ${this.typography.fontSize.xs[1].lineHeight}; }
.text-sm { font-size: ${this.typography.fontSize.sm[0]}; line-height: ${this.typography.fontSize.sm[1].lineHeight}; }
.text-base { font-size: ${this.typography.fontSize.base[0]}; line-height: ${this.typography.fontSize.base[1].lineHeight}; }
.text-lg { font-size: ${this.typography.fontSize.lg[0]}; line-height: ${this.typography.fontSize.lg[1].lineHeight}; }

/* Microsoft 365 Button Styles */
.btn {
    ${Object.entries(this.components.button.base).map(([key, value]) => 
        `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value};`
    ).join('\n    ')}
}

.btn-primary {
    ${Object.entries(this.components.button.variants.primary).map(([key, value]) => 
        `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value};`
    ).join('\n    ')}
}

/* Microsoft 365 Card Styles */
.card {
    ${Object.entries(this.components.card.base).map(([key, value]) => 
        `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value};`
    ).join('\n    ')}
}
        `;
        
        return css;
    }

    /**
     * 🚀 Initialize Theme Application
     */
    initialize() {
        // Apply default theme
        this.applyTheme('light');
        
        // Inject CSS
        const style = document.createElement('style');
        style.textContent = this.generateResponsiveCSS();
        document.head.appendChild(style);
        
        // Setup theme toggle
        this.setupThemeToggle();
        
        console.log('✅ Microsoft 365 Design System fully initialized');
    }

    /**
     * 🎛️ Setup Theme Toggle
     */
    setupThemeToggle() {
        const toggleButton = document.createElement('button');
        toggleButton.className = 'theme-toggle';
        toggleButton.innerHTML = '🌙';
        toggleButton.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        `;
        
        toggleButton.addEventListener('click', () => this.toggleDarkMode());
        document.body.appendChild(toggleButton);
    }

    /**
     * 📊 Get Theme Status
     */
    getThemeStatus() {
        return {
            version: this.version,
            themeName: this.themeName,
            currentMode: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            componentsLoaded: Object.keys(this.components).length,
            colorTokens: Object.keys(this.colorTokens).length,
            typographyStyles: Object.keys(this.typography.styles).length,
            responsive: true,
            accessibility: true
        };
    }
}

// 🚀 Export for integration
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Microsoft365ThemeSystem;
}

// 🌟 Auto-initialize if in browser
if (typeof window !== 'undefined') {
    window.Microsoft365ThemeSystem = Microsoft365ThemeSystem;
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.microsoft365Theme = new Microsoft365ThemeSystem();
            window.microsoft365Theme.initialize();
        });
    } else {
        window.microsoft365Theme = new Microsoft365ThemeSystem();
        window.microsoft365Theme.initialize();
    }
}
