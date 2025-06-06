// Microsoft 365 Design System Implementation
// Academic Requirements: Design System Compliance
// Date: June 6, 2025 - Active Implementation

export interface Microsoft365Colors {
  primary: {
    blue: string;
    green: string;
    red: string;
  };
  secondary: {
    lightBlue: string;
    lightGreen: string;
    lightRed: string;
  };
  neutral: {
    gray50: string;
    gray100: string;
    gray200: string;
    gray300: string;
    gray400: string;
    gray500: string;
    gray600: string;
    gray700: string;
    gray800: string;
    gray900: string;
  };
  surface: {
    background: string;
    card: string;
    elevated: string;
  };
}

export interface Microsoft365Typography {
  fonts: string[];
  sizes: {
    xs: string;
    sm: string;
    base: string;
    lg: string;
    xl: string;
    '2xl': string;
    '3xl': string;
    '4xl': string;
  };
  weights: {
    light: number;
    normal: number;
    medium: number;
    semibold: number;
    bold: number;
  };
  lineHeights: {
    tight: number;
    normal: number;
    relaxed: number;
  };
}

export interface Microsoft365Spacing {
  xs: string;
  sm: string;
  md: string;
  lg: string;
  xl: string;
  '2xl': string;
  '3xl': string;
  '4xl': string;
}

export interface Microsoft365Theme {
  colors: Microsoft365Colors;
  typography: Microsoft365Typography;
  spacing: Microsoft365Spacing;
  borderRadius: {
    none: string;
    sm: string;
    base: string;
    md: string;
    lg: string;
    xl: string;
    full: string;
  };
  shadows: {
    sm: string;
    base: string;
    md: string;
    lg: string;
    xl: string;
  };
  animation: {
    duration: {
      fast: string;
      base: string;
      slow: string;
    };
    easing: {
      ease: string;
      easeIn: string;
      easeOut: string;
      easeInOut: string;
    };
  };
}

export const Microsoft365Theme: Microsoft365Theme = {
  colors: {
    primary: {
      blue: '#2563eb',    // Microsoft Blue - Academic Compliance
      green: '#059669',   // Success Green - Academic Standards
      red: '#dc2626'      // Alert Red - Academic Alerts
    },
    secondary: {
      lightBlue: '#3b82f6',
      lightGreen: '#10b981',
      lightRed: '#ef4444'
    },
    neutral: {
      gray50: '#f9fafb',
      gray100: '#f3f4f6',
      gray200: '#e5e7eb',
      gray300: '#d1d5db',
      gray400: '#9ca3af',
      gray500: '#6b7280',
      gray600: '#4b5563',
      gray700: '#374151',
      gray800: '#1f2937',
      gray900: '#111827'
    },
    surface: {
      background: '#ffffff',
      card: '#f8fafc',
      elevated: '#ffffff'
    }
  },
  typography: {
    fonts: ['Segoe UI', 'Roboto', 'system-ui', 'sans-serif'],
    sizes: {
      xs: '0.75rem',
      sm: '0.875rem',
      base: '1rem',
      lg: '1.125rem',
      xl: '1.25rem',
      '2xl': '1.5rem',
      '3xl': '1.875rem',
      '4xl': '2.25rem'
    },
    weights: {
      light: 300,
      normal: 400,
      medium: 500,
      semibold: 600,
      bold: 700
    },
    lineHeights: {
      tight: 1.25,
      normal: 1.5,
      relaxed: 1.75
    }
  },
  spacing: {
    xs: '0.25rem',
    sm: '0.5rem',
    md: '1rem',
    lg: '1.5rem',
    xl: '2rem',
    '2xl': '2.5rem',
    '3xl': '3rem',
    '4xl': '4rem'
  },
  borderRadius: {
    none: '0',
    sm: '0.125rem',
    base: '0.25rem',
    md: '0.375rem',
    lg: '0.5rem',
    xl: '0.75rem',
    full: '9999px'
  },
  shadows: {
    sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
    base: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
    md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
    lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)'
  },
  animation: {
    duration: {
      fast: '150ms',
      base: '300ms',
      slow: '500ms'
    },
    easing: {
      ease: 'cubic-bezier(0.4, 0, 0.2, 1)',
      easeIn: 'cubic-bezier(0.4, 0, 1, 1)',
      easeOut: 'cubic-bezier(0, 0, 0.2, 1)',
      easeInOut: 'cubic-bezier(0.4, 0, 0.2, 1)'
    }
  }
};

// Academic Compliance Utilities
export const getAcademicColorScheme = (mode: 'light' | 'dark' = 'light') => {
  const theme = Microsoft365Theme;
  
  if (mode === 'dark') {
    return {
      primary: theme.colors.primary.blue,
      secondary: theme.colors.secondary.lightBlue,
      background: theme.colors.neutral.gray900,
      surface: theme.colors.neutral.gray800,
      text: theme.colors.neutral.gray100,
      textSecondary: theme.colors.neutral.gray300
    };
  }
  
  return {
    primary: theme.colors.primary.blue,
    secondary: theme.colors.secondary.lightBlue,
    background: theme.colors.surface.background,
    surface: theme.colors.surface.card,
    text: theme.colors.neutral.gray900,
    textSecondary: theme.colors.neutral.gray600
  };
};

// Academic Component Styles
export const academicComponentStyles = {
  button: {
    primary: {
      backgroundColor: Microsoft365Theme.colors.primary.blue,
      color: Microsoft365Theme.colors.surface.background,
      padding: `${Microsoft365Theme.spacing.sm} ${Microsoft365Theme.spacing.md}`,
      borderRadius: Microsoft365Theme.borderRadius.base,
      fontWeight: Microsoft365Theme.typography.weights.semibold,
      fontSize: Microsoft365Theme.typography.sizes.base,
      boxShadow: Microsoft365Theme.shadows.sm,
      transition: `all ${Microsoft365Theme.animation.duration.base} ${Microsoft365Theme.animation.easing.ease}`
    },
    secondary: {
      backgroundColor: Microsoft365Theme.colors.surface.background,
      color: Microsoft365Theme.colors.primary.blue,
      border: `1px solid ${Microsoft365Theme.colors.primary.blue}`,
      padding: `${Microsoft365Theme.spacing.sm} ${Microsoft365Theme.spacing.md}`,
      borderRadius: Microsoft365Theme.borderRadius.base,
      fontWeight: Microsoft365Theme.typography.weights.semibold,
      fontSize: Microsoft365Theme.typography.sizes.base,
      transition: `all ${Microsoft365Theme.animation.duration.base} ${Microsoft365Theme.animation.easing.ease}`
    }
  },
  card: {
    backgroundColor: Microsoft365Theme.colors.surface.card,
    borderRadius: Microsoft365Theme.borderRadius.lg,
    padding: Microsoft365Theme.spacing.lg,
    boxShadow: Microsoft365Theme.shadows.base,
    border: `1px solid ${Microsoft365Theme.colors.neutral.gray200}`
  },
  input: {
    backgroundColor: Microsoft365Theme.colors.surface.background,
    border: `1px solid ${Microsoft365Theme.colors.neutral.gray300}`,
    borderRadius: Microsoft365Theme.borderRadius.base,
    padding: `${Microsoft365Theme.spacing.sm} ${Microsoft365Theme.spacing.md}`,
    fontSize: Microsoft365Theme.typography.sizes.base,
    fontFamily: Microsoft365Theme.typography.fonts.join(', '),
    color: Microsoft365Theme.colors.neutral.gray900,
    transition: `all ${Microsoft365Theme.animation.duration.base} ${Microsoft365Theme.animation.easing.ease}`
  }
};

export default Microsoft365Theme;
