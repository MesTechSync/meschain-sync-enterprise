/**
 * Microsoft 365 Advanced Design System
 * Complete theme configuration for enterprise-grade applications
 * 
 * @version 2.0.0
 * @author MesChain Sync Team
 * @description Advanced design tokens following Microsoft 365 design principles
 */

// Color Palette - Microsoft 365 Inspired
export const MS365Colors = {
  // Primary Palette
  primary: {
    blue: {
      50: '#ebf3ff',
      100: '#d1e7ff', 
      200: '#a6d2ff',
      300: '#70b7ff',
      400: '#2889ff',
      500: '#2563eb', // Main brand blue
      600: '#1d4ed8',
      700: '#1e40af',
      800: '#1e3a8a',
      900: '#1e3a8a'
    },
    green: {
      50: '#ecfccb',
      100: '#d9f99d',
      200: '#bef264',
      300: '#a3e635',
      400: '#84cc16',
      500: '#059669', // Success green
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
      500: '#dc2626', // Error red
      600: '#b91c1c',
      700: '#991b1b',
      800: '#7f1d1d',
      900: '#7f1d1d'
    },
    purple: {
      50: '#faf5ff',
      100: '#f3e8ff',
      200: '#e9d5ff',
      300: '#d8b4fe',
      400: '#c084fc',
      500: '#a855f7',
      600: '#9333ea',
      700: '#7c3aed',
      800: '#6b21a8',
      900: '#581c87'
    },
    orange: {
      50: '#fff7ed',
      100: '#ffedd5',
      200: '#fed7aa',
      300: '#fdba74',
      400: '#fb923c',
      500: '#f97316',
      600: '#ea580c',
      700: '#c2410c',
      800: '#9a3412',
      900: '#7c2d12'
    }
  },

  // Neutral Palette
  neutral: {
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
  },

  // Semantic Colors
  semantic: {
    success: '#059669',
    warning: '#d97706',
    error: '#dc2626',
    info: '#2563eb',
    // Status indicators
    online: '#10b981',
    offline: '#ef4444',
    pending: '#f59e0b',
    processing: '#8b5cf6'
  },

  // Background Variations
  background: {
    primary: '#ffffff',
    secondary: '#f8fafc',
    tertiary: '#f1f5f9',
    dark: '#0f172a',
    darkSecondary: '#1e293b',
    overlay: 'rgba(0, 0, 0, 0.6)'
  }
};

// Typography System
export const MS365Typography = {
  fonts: {
    system: '-apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif',
    mono: 'ui-monospace, SFMono-Regular, "SF Mono", Monaco, Consolas, "Liberation Mono", "Courier New", monospace'
  },
  
  sizes: {
    xs: '0.75rem',    // 12px
    sm: '0.875rem',   // 14px  
    base: '1rem',     // 16px
    lg: '1.125rem',   // 18px
    xl: '1.25rem',    // 20px
    '2xl': '1.5rem',  // 24px
    '3xl': '1.875rem', // 30px
    '4xl': '2.25rem',  // 36px
    '5xl': '3rem'      // 48px
  },
  
  weights: {
    light: 300,
    normal: 400,
    medium: 500,
    semibold: 600,
    bold: 700,
    extrabold: 800
  },
  
  lineHeights: {
    tight: 1.25,
    normal: 1.5,
    relaxed: 1.75,
    loose: 2
  },
  
  letterSpacing: {
    tighter: '-0.05em',
    tight: '-0.025em',
    normal: '0em',
    wide: '0.025em',
    wider: '0.05em',
    widest: '0.1em'
  }
};

// Spacing System
export const MS365Spacing = {
  0: '0',
  1: '0.25rem',   // 4px
  2: '0.5rem',    // 8px
  3: '0.75rem',   // 12px
  4: '1rem',      // 16px
  5: '1.25rem',   // 20px
  6: '1.5rem',    // 24px
  7: '1.75rem',   // 28px
  8: '2rem',      // 32px
  10: '2.5rem',   // 40px
  12: '3rem',     // 48px
  16: '4rem',     // 64px
  20: '5rem',     // 80px
  24: '6rem',     // 96px
  32: '8rem',     // 128px
  40: '10rem',    // 160px
  48: '12rem',    // 192px
  56: '14rem',    // 224px
  64: '16rem'     // 256px
};

// Component System
export const AdvancedMS365Theme = {
  components: {
    // Button System
    buttons: {
      variants: {
        primary: {
          backgroundColor: MS365Colors.primary.blue[500],
          color: '#ffffff',
          border: 'none',
          hover: {
            backgroundColor: MS365Colors.primary.blue[600],
            transform: 'translateY(-1px)',
            boxShadow: '0 4px 12px rgba(37, 99, 235, 0.3)'
          },
          active: {
            backgroundColor: MS365Colors.primary.blue[700],
            transform: 'translateY(0)'
          },
          disabled: {
            backgroundColor: MS365Colors.neutral[300],
            color: MS365Colors.neutral[500],
            cursor: 'not-allowed'
          }
        },
        secondary: {
          backgroundColor: MS365Colors.primary.green[500],
          color: '#ffffff',
          border: 'none',
          hover: {
            backgroundColor: MS365Colors.primary.green[600],
            transform: 'translateY(-1px)'
          }
        },
        ghost: {
          backgroundColor: 'transparent',
          color: MS365Colors.neutral[600],
          border: `1px solid ${MS365Colors.neutral[300]}`,
          hover: {
            backgroundColor: MS365Colors.neutral[50],
            borderColor: MS365Colors.neutral[400]
          }
        },
        destructive: {
          backgroundColor: MS365Colors.primary.red[500],
          color: '#ffffff',
          border: 'none',
          hover: {
            backgroundColor: MS365Colors.primary.red[600]
          }
        }
      },
      
      sizes: {
        sm: {
          padding: `${MS365Spacing[2]} ${MS365Spacing[3]}`,
          fontSize: MS365Typography.sizes.sm,
          borderRadius: '6px'
        },
        md: {
          padding: `${MS365Spacing[3]} ${MS365Spacing[4]}`,
          fontSize: MS365Typography.sizes.base,
          borderRadius: '8px'
        },
        lg: {
          padding: `${MS365Spacing[4]} ${MS365Spacing[6]}`,
          fontSize: MS365Typography.sizes.lg,
          borderRadius: '10px'
        },
        xl: {
          padding: `${MS365Spacing[5]} ${MS365Spacing[8]}`,
          fontSize: MS365Typography.sizes.xl,
          borderRadius: '12px'
        }
      }
    },

    // Card System
    cards: {
      elevations: {
        0: 'none',
        1: '0 1px 2px rgba(0, 0, 0, 0.05)',
        2: '0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06)',
        4: '0 4px 6px rgba(0, 0, 0, 0.05), 0 2px 4px rgba(0, 0, 0, 0.08)',
        8: '0 8px 25px rgba(0, 0, 0, 0.1), 0 4px 10px rgba(0, 0, 0, 0.05)',
        16: '0 20px 40px rgba(0, 0, 0, 0.1), 0 8px 16px rgba(0, 0, 0, 0.06)'
      },
      
      radiuses: {
        none: '0',
        sm: '6px',
        md: '8px',
        lg: '12px',
        xl: '16px',
        full: '9999px'
      },
      
      animations: {
        slideIn: {
          keyframes: `
            @keyframes slideIn {
              from { transform: translateY(20px); opacity: 0; }
              to { transform: translateY(0); opacity: 1; }
            }
          `,
          animation: 'slideIn 0.3s ease-out'
        },
        fadeIn: {
          keyframes: `
            @keyframes fadeIn {
              from { opacity: 0; }
              to { opacity: 1; }
            }
          `,
          animation: 'fadeIn 0.2s ease-out'
        },
        scaleIn: {
          keyframes: `
            @keyframes scaleIn {
              from { transform: scale(0.95); opacity: 0; }
              to { transform: scale(1); opacity: 1; }
            }
          `,
          animation: 'scaleIn 0.2s ease-out'
        }
      },
      
      variants: {
        default: {
          backgroundColor: MS365Colors.background.primary,
          border: `1px solid ${MS365Colors.neutral[200]}`,
          borderRadius: '12px',
          padding: MS365Spacing[6],
          transition: 'all 0.2s ease'
        },
        elevated: {
          backgroundColor: MS365Colors.background.primary,
          boxShadow: '0 4px 6px rgba(0, 0, 0, 0.05)',
          borderRadius: '12px',
          padding: MS365Spacing[6],
          hover: {
            transform: 'translateY(-2px)',
            boxShadow: '0 8px 25px rgba(0, 0, 0, 0.1)'
          }
        }
      }
    },

    // Form System
    forms: {
      input: {
        base: {
          padding: `${MS365Spacing[3]} ${MS365Spacing[4]}`,
          borderRadius: '8px',
          border: `1px solid ${MS365Colors.neutral[300]}`,
          fontSize: MS365Typography.sizes.base,
          fontFamily: MS365Typography.fonts.system,
          transition: 'all 0.2s ease',
          backgroundColor: MS365Colors.background.primary
        },
        focus: {
          borderColor: MS365Colors.primary.blue[500],
          boxShadow: `0 0 0 3px rgba(37, 99, 235, 0.1)`,
          outline: 'none'
        },
        error: {
          borderColor: MS365Colors.primary.red[500],
          boxShadow: `0 0 0 3px rgba(220, 38, 38, 0.1)`
        },
        success: {
          borderColor: MS365Colors.primary.green[500],
          boxShadow: `0 0 0 3px rgba(5, 150, 105, 0.1)`
        }
      },
      
      label: {
        base: {
          fontSize: MS365Typography.sizes.sm,
          fontWeight: MS365Typography.weights.medium,
          color: MS365Colors.neutral[700],
          marginBottom: MS365Spacing[2]
        }
      }
    }
  },

  // Accessibility Configuration
  accessibility: {
    contrast: 'WCAG-AA',
    focusManagement: true,
    screenReader: true,
    reducedMotion: {
      respectPreference: true,
      fallbackAnimations: {
        duration: '0.01ms',
        fillMode: 'both'
      }
    },
    
    focusStyles: {
      outline: `2px solid ${MS365Colors.primary.blue[500]}`,
      outlineOffset: '2px',
      borderRadius: '4px'
    },
    
    highContrast: {
      colors: {
        text: '#000000',
        background: '#ffffff',
        border: '#000000',
        focus: '#0066cc'
      }
    }
  },

  // Breakpoints for Responsive Design
  breakpoints: {
    xs: '320px',
    sm: '640px',
    md: '768px',
    lg: '1024px',
    xl: '1280px',
    '2xl': '1536px'
  },

  // Animation System
  animations: {
    durations: {
      fast: '150ms',
      normal: '300ms',
      slow: '500ms'
    },
    
    easings: {
      easeOut: 'cubic-bezier(0.0, 0.0, 0.2, 1)',
      easeIn: 'cubic-bezier(0.4, 0.0, 1, 1)',
      easeInOut: 'cubic-bezier(0.4, 0.0, 0.2, 1)',
      bounce: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'
    }
  },

  // Dark Mode Support
  darkMode: {
    colors: {
      background: {
        primary: '#0f172a',
        secondary: '#1e293b',
        tertiary: '#334155'
      },
      text: {
        primary: '#f8fafc',
        secondary: '#cbd5e1',
        tertiary: '#94a3b8'
      },
      border: '#334155'
    }
  }
};

// Utility Functions
export const MS365Utils = {
  // Get color with opacity
  getColorWithOpacity: (color: string, opacity: number): string => {
    return `${color}${Math.round(opacity * 255).toString(16).padStart(2, '0')}`;
  },

  // Generate responsive styles
  generateResponsiveStyles: (property: string, values: Record<string, string>) => {
    const { breakpoints } = AdvancedMS365Theme;
    let styles = '';
    
    Object.entries(values).forEach(([breakpoint, value]) => {
      if (breakpoint === 'base') {
        styles += `${property}: ${value};\n`;
      } else {
        const minWidth = breakpoints[breakpoint as keyof typeof breakpoints];
        styles += `@media (min-width: ${minWidth}) { ${property}: ${value}; }\n`;
      }
    });
    
    return styles;
  },

  // Generate component variant styles
  generateVariantStyles: (baseStyles: any, variant: any) => {
    return { ...baseStyles, ...variant };
  }
};

// Export default theme object
export default AdvancedMS365Theme; 