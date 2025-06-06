/**
 * 🎨 Microsoft 365 Design System v10.0
 * Academic Requirements Implementation
 * Cursor Team - Premium Visual Excellence
 * Date: June 5, 2025
 */

export const Microsoft365DesignSystem = {
  // 🎯 Academic Requirement: Exact color palette from documents
  colors: {
    primary: {
      // Microsoft 365 primary blue - academic requirement
      main: '#2563eb',
      light: '#3b82f6',
      lighter: '#dbeafe',
      dark: '#1d4ed8',
      darker: '#1e40af',
      contrast: '#ffffff'
    },
    success: {
      // Academic requirement: #059669 green
      main: '#059669',
      light: '#10b981',
      lighter: '#d1fae5',
      dark: '#047857',
      darker: '#065f46',
      contrast: '#ffffff'
    },
    error: {
      // Academic requirement: #dc2626 red
      main: '#dc2626',
      light: '#ef4444',
      lighter: '#fee2e2',
      dark: '#b91c1c',
      darker: '#991b1b',
      contrast: '#ffffff'
    },
    warning: {
      main: '#d97706',
      light: '#f59e0b',
      lighter: '#fef3c7',
      dark: '#b45309',
      contrast: '#ffffff'
    },
    info: {
      main: '#0ea5e9',
      light: '#38bdf8',
      lighter: '#e0f2fe',
      dark: '#0284c7',
      contrast: '#ffffff'
    },
    neutral: {
      // Academic requirement: "yüksek aydınlık" - high brightness approach
      gray50: '#f9fafb',
      gray100: '#f3f4f6',
      gray200: '#e5e7eb',
      gray300: '#d1d5db',
      gray400: '#9ca3af',
      gray500: '#6b7280',
      gray600: '#4b5563',
      gray700: '#374151',
      gray800: '#1f2937',
      gray900: '#111827',
      white: '#ffffff',
      black: '#000000'
    }
  },

  // 🔤 Academic Requirement: "Net küçük yazı karakterleri"
  typography: {
    fontFamily: {
      primary: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
      secondary: '"Inter", sans-serif',
      monospace: '"SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace'
    },
    fontSize: {
      // Academic requirement: Small, clean typography
      xs: '0.75rem',    // 12px - very small, clean
      sm: '0.875rem',   // 14px - small, readable
      base: '1rem',     // 16px - standard
      lg: '1.125rem',   // 18px
      xl: '1.25rem',    // 20px
      '2xl': '1.5rem',  // 24px
      '3xl': '1.875rem' // 30px
    },
    fontWeight: {
      light: 300,
      normal: 400,
      medium: 500,
      semibold: 600,
      bold: 700,
      extrabold: 800
    },
    lineHeight: {
      tight: 1.25,
      snug: 1.375,
      normal: 1.5,
      relaxed: 1.625,
      loose: 2
    },
    // Academic requirement: High readability
    textOpacity: {
      primary: 0.95,
      secondary: 0.75,
      disabled: 0.50
    }
  },

  // 📐 Spacing system - Microsoft 365 style
  spacing: {
    px: '1px',
    0: '0',
    0.5: '0.125rem',  // 2px
    1: '0.25rem',     // 4px
    1.5: '0.375rem',  // 6px
    2: '0.5rem',      // 8px
    2.5: '0.625rem',  // 10px
    3: '0.75rem',     // 12px
    3.5: '0.875rem',  // 14px
    4: '1rem',        // 16px
    5: '1.25rem',     // 20px
    6: '1.5rem',      // 24px
    7: '1.75rem',     // 28px
    8: '2rem',        // 32px
    9: '2.25rem',     // 36px
    10: '2.5rem',     // 40px
    12: '3rem',       // 48px
    16: '4rem',       // 64px
    20: '5rem',       // 80px
    24: '6rem'        // 96px
  },

  // 🔄 Border radius - Microsoft 365 rounded corners
  borderRadius: {
    none: '0',
    sm: '0.375rem',   // 6px
    default: '0.5rem', // 8px
    md: '0.75rem',    // 12px
    lg: '1rem',       // 16px
    xl: '1.5rem',     // 24px
    '2xl': '2rem',    // 32px
    full: '9999px'
  },

  // 🌟 Shadows - Academic requirement: "subtle shadows"
  shadows: {
    // Microsoft 365 style subtle shadows
    xs: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
    sm: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
    md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
    lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
    '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
    inner: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)'
  },

  // ⚡ Transitions - Academic requirement: "Fluid animations"
  transitions: {
    // Microsoft 365 smooth transitions
    fast: '150ms cubic-bezier(0.4, 0, 0.2, 1)',
    normal: '250ms cubic-bezier(0.4, 0, 0.2, 1)',
    slow: '350ms cubic-bezier(0.4, 0, 0.2, 1)',
    bounce: '500ms cubic-bezier(0.68, -0.55, 0.265, 1.55)'
  },

  // 📏 Z-index scale
  zIndex: {
    hide: -1,
    auto: 'auto',
    base: 0,
    docked: 10,
    dropdown: 1000,
    sticky: 1020,
    banner: 1030,
    overlay: 1040,
    modal: 1050,
    popover: 1060,
    skipLink: 1070,
    toast: 1080,
    tooltip: 1090
  },

  // 📱 Breakpoints - Mobile first
  breakpoints: {
    xs: '0px',
    sm: '576px',
    md: '768px',
    lg: '992px',
    xl: '1200px',
    '2xl': '1400px'
  }
};

// 🎨 Academic Requirement: Component Design Tokens
export const MS365ComponentTokens = {
  // Card components - academic requirement
  card: {
    background: Microsoft365DesignSystem.colors.neutral.white,
    border: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray200}`,
    borderRadius: Microsoft365DesignSystem.borderRadius.md,
    shadow: Microsoft365DesignSystem.shadows.sm,
    padding: Microsoft365DesignSystem.spacing[6],
    hoverShadow: Microsoft365DesignSystem.shadows.md,
    transition: Microsoft365DesignSystem.transitions.normal
  },

  // Button components
  button: {
    borderRadius: Microsoft365DesignSystem.borderRadius.default,
    padding: `${Microsoft365DesignSystem.spacing[2.5]} ${Microsoft365DesignSystem.spacing[4]}`,
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    fontWeight: Microsoft365DesignSystem.typography.fontWeight.medium,
    transition: Microsoft365DesignSystem.transitions.fast,
    shadow: Microsoft365DesignSystem.shadows.sm,
    hoverShadow: Microsoft365DesignSystem.shadows.md
  },

  // Input components
  input: {
    borderRadius: Microsoft365DesignSystem.borderRadius.default,
    border: `1px solid ${Microsoft365DesignSystem.colors.neutral.gray300}`,
    padding: `${Microsoft365DesignSystem.spacing[2.5]} ${Microsoft365DesignSystem.spacing[3]}`,
    fontSize: Microsoft365DesignSystem.typography.fontSize.sm,
    transition: Microsoft365DesignSystem.transitions.fast,
    focusBorder: Microsoft365DesignSystem.colors.primary.main,
    focusShadow: `0 0 0 3px ${Microsoft365DesignSystem.colors.primary.lighter}`
  }
};

// 🌙 Dark mode support
export const MS365DarkMode = {
  colors: {
    primary: {
      main: '#3b82f6',
      light: '#60a5fa',
      lighter: '#1e3a8a',
      dark: '#2563eb',
      contrast: '#ffffff'
    },
    background: {
      primary: '#1f2937',
      secondary: '#374151',
      tertiary: '#4b5563'
    },
    text: {
      primary: '#f9fafb',
      secondary: '#d1d5db',
      muted: '#9ca3af'
    },
    border: '#4b5563'
  }
};

export default Microsoft365DesignSystem; 