/**
 * Advanced Theme Provider with Dynamic Customization
 * Supports dark/light themes, custom colors, accessibility, and user preferences
 */

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { 
  createTheme, 
  ThemeProvider as MUIThemeProvider, 
  ThemeOptions, 
  Theme,
  alpha 
} from '@mui/material/styles';
import { CssBaseline } from '@mui/material';

// Types
interface ThemeConfig {
  mode: 'light' | 'dark' | 'auto';
  primaryColor: string;
  secondaryColor: string;
  accentColor: string;
  borderRadius: number;
  fontSize: 'small' | 'medium' | 'large';
  density: 'comfortable' | 'compact' | 'spacious';
  animations: boolean;
  reducedMotion: boolean;
  highContrast: boolean;
  customColors?: {
    success?: string;
    warning?: string;
    error?: string;
    info?: string;
  };
}

interface ThemeContextType {
  themeConfig: ThemeConfig;
  currentTheme: Theme;
  updateTheme: (config: Partial<ThemeConfig>) => void;
  resetTheme: () => void;
  exportTheme: () => string;
  importTheme: (themeData: string) => void;
  availablePresets: ThemePreset[];
  applyPreset: (presetId: string) => void;
}

interface ThemePreset {
  id: string;
  name: string;
  description: string;
  config: ThemeConfig;
  preview: {
    primary: string;
    secondary: string;
    background: string;
  };
}

// Default theme configuration
const defaultThemeConfig: ThemeConfig = {
  mode: 'auto',
  primaryColor: '#1976d2',
  secondaryColor: '#dc004e',
  accentColor: '#ed6c02',
  borderRadius: 8,
  fontSize: 'medium',
  density: 'comfortable',
  animations: true,
  reducedMotion: false,
  highContrast: false,
  customColors: {
    success: '#2e7d32',
    warning: '#ed6c02',
    error: '#d32f2f',
    info: '#0288d1'
  }
};

// Theme presets
const themePresets: ThemePreset[] = [
  {
    id: 'modern_blue',
    name: 'Modern Blue',
    description: 'Clean and professional blue theme',
    config: {
      ...defaultThemeConfig,
      primaryColor: '#1976d2',
      secondaryColor: '#dc004e',
      accentColor: '#ed6c02'
    },
    preview: {
      primary: '#1976d2',
      secondary: '#dc004e',
      background: '#ffffff'
    }
  },
  {
    id: 'emerald_green',
    name: 'Emerald Green',
    description: 'Nature-inspired green theme',
    config: {
      ...defaultThemeConfig,
      primaryColor: '#059669',
      secondaryColor: '#7c3aed',
      accentColor: '#f59e0b'
    },
    preview: {
      primary: '#059669',
      secondary: '#7c3aed',
      background: '#ffffff'
    }
  },
  {
    id: 'purple_passion',
    name: 'Purple Passion',
    description: 'Elegant purple and pink theme',
    config: {
      ...defaultThemeConfig,
      primaryColor: '#7c3aed',
      secondaryColor: '#ec4899',
      accentColor: '#f97316'
    },
    preview: {
      primary: '#7c3aed',
      secondary: '#ec4899',
      background: '#ffffff'
    }
  },
  {
    id: 'sunset_orange',
    name: 'Sunset Orange',
    description: 'Warm and energetic orange theme',
    config: {
      ...defaultThemeConfig,
      primaryColor: '#ea580c',
      secondaryColor: '#dc2626',
      accentColor: '#7c3aed'
    },
    preview: {
      primary: '#ea580c',
      secondary: '#dc2626',
      background: '#ffffff'
    }
  },
  {
    id: 'dark_professional',
    name: 'Dark Professional',
    description: 'Sleek dark theme for professionals',
    config: {
      ...defaultThemeConfig,
      mode: 'dark',
      primaryColor: '#3b82f6',
      secondaryColor: '#10b981',
      accentColor: '#f59e0b'
    },
    preview: {
      primary: '#3b82f6',
      secondary: '#10b981',
      background: '#111827'
    }
  },
  {
    id: 'high_contrast',
    name: 'High Contrast',
    description: 'Maximum accessibility theme',
    config: {
      ...defaultThemeConfig,
      primaryColor: '#000000',
      secondaryColor: '#ffffff',
      accentColor: '#ffff00',
      highContrast: true,
      borderRadius: 4
    },
    preview: {
      primary: '#000000',
      secondary: '#ffffff',
      background: '#ffffff'
    }
  }
];

// Theme context
const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

export const useAdvancedTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useAdvancedTheme must be used within AdvancedThemeProvider');
  }
  return context;
};

// Theme provider component
interface AdvancedThemeProviderProps {
  children: ReactNode;
  persistTheme?: boolean;
  storageKey?: string;
}

export const AdvancedThemeProvider: React.FC<AdvancedThemeProviderProps> = ({
  children,
  persistTheme = true,
  storageKey = 'meschain_theme_config'
}) => {
  const [themeConfig, setThemeConfig] = useState<ThemeConfig>(defaultThemeConfig);
  const [systemDarkMode, setSystemDarkMode] = useState(false);

  // Load theme from localStorage on mount
  useEffect(() => {
    if (persistTheme) {
      const savedTheme = localStorage.getItem(storageKey);
      if (savedTheme) {
        try {
          const parsedTheme = JSON.parse(savedTheme);
          setThemeConfig({ ...defaultThemeConfig, ...parsedTheme });
        } catch (error) {
          console.warn('Failed to parse saved theme config:', error);
        }
      }
    }

    // Detect system dark mode preference
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    setSystemDarkMode(mediaQuery.matches);

    const handleChange = (e: MediaQueryListEvent) => {
      setSystemDarkMode(e.matches);
    };

    mediaQuery.addEventListener('change', handleChange);
    return () => mediaQuery.removeEventListener('change', handleChange);
  }, [persistTheme, storageKey]);

  // Save theme to localStorage when it changes
  useEffect(() => {
    if (persistTheme) {
      localStorage.setItem(storageKey, JSON.stringify(themeConfig));
    }
  }, [themeConfig, persistTheme, storageKey]);

  // Determine actual theme mode
  const getActualThemeMode = (): 'light' | 'dark' => {
    if (themeConfig.mode === 'auto') {
      return systemDarkMode ? 'dark' : 'light';
    }
    return themeConfig.mode;
  };

  // Create MUI theme based on configuration
  const createAdvancedTheme = (): Theme => {
    const mode = getActualThemeMode();
    const isDark = mode === 'dark';

    // Font size mapping
    const fontSizeMap = {
      small: {
        fontSize: 13,
        h1: '2rem',
        h2: '1.75rem',
        h3: '1.5rem',
        h4: '1.25rem',
        h5: '1.125rem',
        h6: '1rem'
      },
      medium: {
        fontSize: 14,
        h1: '2.5rem',
        h2: '2rem',
        h3: '1.75rem',
        h4: '1.5rem',
        h5: '1.25rem',
        h6: '1.125rem'
      },
      large: {
        fontSize: 16,
        h1: '3rem',
        h2: '2.5rem',
        h3: '2rem',
        h4: '1.75rem',
        h5: '1.5rem',
        h6: '1.25rem'
      }
    };

    // Density mapping
    const densityMap = {
      compact: {
        spacing: 6,
        buttonPadding: '4px 8px',
        inputHeight: 32
      },
      comfortable: {
        spacing: 8,
        buttonPadding: '8px 16px',
        inputHeight: 40
      },
      spacious: {
        spacing: 12,
        buttonPadding: '12px 24px',
        inputHeight: 48
      }
    };

    const fontConfig = fontSizeMap[themeConfig.fontSize];
    const densityConfig = densityMap[themeConfig.density];

    const themeOptions: ThemeOptions = {
      palette: {
        mode,
        primary: {
          main: themeConfig.primaryColor,
          light: alpha(themeConfig.primaryColor, 0.7),
          dark: alpha(themeConfig.primaryColor, 0.9),
          contrastText: isDark ? '#ffffff' : '#000000'
        },
        secondary: {
          main: themeConfig.secondaryColor,
          light: alpha(themeConfig.secondaryColor, 0.7),
          dark: alpha(themeConfig.secondaryColor, 0.9),
          contrastText: isDark ? '#ffffff' : '#000000'
        },
        success: {
          main: themeConfig.customColors?.success || '#2e7d32'
        },
        warning: {
          main: themeConfig.customColors?.warning || '#ed6c02'
        },
        error: {
          main: themeConfig.customColors?.error || '#d32f2f'
        },
        info: {
          main: themeConfig.customColors?.info || '#0288d1'
        },
        background: {
          default: isDark 
            ? (themeConfig.highContrast ? '#000000' : '#121212')
            : (themeConfig.highContrast ? '#ffffff' : '#fafafa'),
          paper: isDark 
            ? (themeConfig.highContrast ? '#1a1a1a' : '#1e1e1e')
            : (themeConfig.highContrast ? '#ffffff' : '#ffffff')
        },
        text: {
          primary: isDark
            ? (themeConfig.highContrast ? '#ffffff' : 'rgba(255, 255, 255, 0.87)')
            : (themeConfig.highContrast ? '#000000' : 'rgba(0, 0, 0, 0.87)'),
          secondary: isDark
            ? (themeConfig.highContrast ? '#cccccc' : 'rgba(255, 255, 255, 0.6)')
            : (themeConfig.highContrast ? '#333333' : 'rgba(0, 0, 0, 0.6)')
        }
      },
      typography: {
        fontSize: fontConfig.fontSize,
        h1: { fontSize: fontConfig.h1, fontWeight: 700 },
        h2: { fontSize: fontConfig.h2, fontWeight: 600 },
        h3: { fontSize: fontConfig.h3, fontWeight: 600 },
        h4: { fontSize: fontConfig.h4, fontWeight: 500 },
        h5: { fontSize: fontConfig.h5, fontWeight: 500 },
        h6: { fontSize: fontConfig.h6, fontWeight: 500 },
        fontFamily: '"Inter", "Roboto", "Helvetica", "Arial", sans-serif'
      },
      spacing: densityConfig.spacing,
      shape: {
        borderRadius: themeConfig.borderRadius
      },
      components: {
        MuiCssBaseline: {
          styleOverrides: {
            '*': {
              scrollbarWidth: 'thin',
              scrollbarColor: `${alpha(themeConfig.primaryColor, 0.3)} transparent`,
              '&::-webkit-scrollbar': {
                width: '8px',
                height: '8px'
              },
              '&::-webkit-scrollbar-track': {
                background: 'transparent'
              },
              '&::-webkit-scrollbar-thumb': {
                background: alpha(themeConfig.primaryColor, 0.3),
                borderRadius: '4px',
                '&:hover': {
                  background: alpha(themeConfig.primaryColor, 0.5)
                }
              }
            },
            body: {
              fontSize: fontConfig.fontSize,
              lineHeight: 1.6,
              transition: themeConfig.animations && !themeConfig.reducedMotion 
                ? 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)' 
                : 'none'
            }
          }
        },
        MuiButton: {
          styleOverrides: {
            root: {
              padding: densityConfig.buttonPadding,
              textTransform: 'none',
              fontWeight: 500,
              borderRadius: themeConfig.borderRadius,
              transition: themeConfig.animations && !themeConfig.reducedMotion
                ? 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)'
                : 'none',
              '&:hover': {
                transform: themeConfig.animations && !themeConfig.reducedMotion 
                  ? 'translateY(-1px)' 
                  : 'none',
                boxShadow: themeConfig.animations && !themeConfig.reducedMotion
                  ? `0 4px 12px ${alpha(themeConfig.primaryColor, 0.3)}`
                  : 'none'
              }
            }
          }
        },
        MuiCard: {
          styleOverrides: {
            root: {
              borderRadius: themeConfig.borderRadius * 1.5,
              boxShadow: isDark
                ? '0 2px 8px rgba(0, 0, 0, 0.4)'
                : '0 2px 8px rgba(0, 0, 0, 0.1)',
              transition: themeConfig.animations && !themeConfig.reducedMotion
                ? 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                : 'none',
              '&:hover': {
                transform: themeConfig.animations && !themeConfig.reducedMotion 
                  ? 'translateY(-2px)' 
                  : 'none',
                boxShadow: isDark
                  ? '0 8px 24px rgba(0, 0, 0, 0.6)'
                  : '0 8px 24px rgba(0, 0, 0, 0.15)'
              }
            }
          }
        },
        MuiTextField: {
          styleOverrides: {
            root: {
              '& .MuiOutlinedInput-root': {
                height: densityConfig.inputHeight,
                borderRadius: themeConfig.borderRadius,
                transition: themeConfig.animations && !themeConfig.reducedMotion
                  ? 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)'
                  : 'none'
              }
            }
          }
        },
        MuiPaper: {
          styleOverrides: {
            root: {
              borderRadius: themeConfig.borderRadius,
              backgroundImage: 'none'
            }
          }
        },
        MuiAppBar: {
          styleOverrides: {
            root: {
              boxShadow: isDark
                ? '0 2px 8px rgba(0, 0, 0, 0.4)'
                : '0 2px 8px rgba(0, 0, 0, 0.1)'
            }
          }
        },
        MuiDrawer: {
          styleOverrides: {
            paper: {
              borderRight: `1px solid ${alpha(themeConfig.primaryColor, 0.1)}`
            }
          }
        },
        MuiChip: {
          styleOverrides: {
            root: {
              borderRadius: themeConfig.borderRadius / 2,
              transition: themeConfig.animations && !themeConfig.reducedMotion
                ? 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)'
                : 'none'
            }
          }
        },
        MuiTab: {
          styleOverrides: {
            root: {
              textTransform: 'none',
              fontWeight: 500,
              transition: themeConfig.animations && !themeConfig.reducedMotion
                ? 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)'
                : 'none'
            }
          }
        }
      },
      transitions: {
        duration: {
          shortest: themeConfig.reducedMotion ? 0 : 150,
          shorter: themeConfig.reducedMotion ? 0 : 200,
          short: themeConfig.reducedMotion ? 0 : 250,
          standard: themeConfig.reducedMotion ? 0 : 300,
          complex: themeConfig.reducedMotion ? 0 : 375,
          enteringScreen: themeConfig.reducedMotion ? 0 : 225,
          leavingScreen: themeConfig.reducedMotion ? 0 : 195
        }
      }
    };

    return createTheme(themeOptions);
  };

  const currentTheme = createAdvancedTheme();

  // Theme management functions
  const updateTheme = (config: Partial<ThemeConfig>) => {
    setThemeConfig(prev => ({ ...prev, ...config }));
  };

  const resetTheme = () => {
    setThemeConfig(defaultThemeConfig);
  };

  const exportTheme = (): string => {
    return JSON.stringify(themeConfig, null, 2);
  };

  const importTheme = (themeData: string) => {
    try {
      const parsedTheme = JSON.parse(themeData);
      setThemeConfig({ ...defaultThemeConfig, ...parsedTheme });
    } catch (error) {
      console.error('Failed to import theme:', error);
      throw new Error('Invalid theme data format');
    }
  };

  const applyPreset = (presetId: string) => {
    const preset = themePresets.find(p => p.id === presetId);
    if (preset) {
      setThemeConfig(preset.config);
    }
  };

  // Accessibility: Detect reduced motion preference
  useEffect(() => {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    
    const handleReducedMotion = (e: MediaQueryListEvent) => {
      updateTheme({ reducedMotion: e.matches });
    };

    if (mediaQuery.matches) {
      updateTheme({ reducedMotion: true });
    }

    mediaQuery.addEventListener('change', handleReducedMotion);
    return () => mediaQuery.removeEventListener('change', handleReducedMotion);
  }, []);

  const contextValue: ThemeContextType = {
    themeConfig,
    currentTheme,
    updateTheme,
    resetTheme,
    exportTheme,
    importTheme,
    availablePresets: themePresets,
    applyPreset
  };

  return (
    <ThemeContext.Provider value={contextValue}>
      <MUIThemeProvider theme={currentTheme}>
        <CssBaseline />
        {children}
      </MUIThemeProvider>
    </ThemeContext.Provider>
  );
};

// Theme customization hook
export const useThemeCustomization = () => {
  const { themeConfig, updateTheme, currentTheme } = useAdvancedTheme();

  const toggleDarkMode = () => {
    updateTheme({ 
      mode: themeConfig.mode === 'dark' ? 'light' : 'dark' 
    });
  };

  const setAutoMode = () => {
    updateTheme({ mode: 'auto' });
  };

  const updatePrimaryColor = (color: string) => {
    updateTheme({ primaryColor: color });
  };

  const updateSecondaryColor = (color: string) => {
    updateTheme({ secondaryColor: color });
  };

  const updateBorderRadius = (radius: number) => {
    updateTheme({ borderRadius: radius });
  };

  const updateFontSize = (size: 'small' | 'medium' | 'large') => {
    updateTheme({ fontSize: size });
  };

  const updateDensity = (density: 'comfortable' | 'compact' | 'spacious') => {
    updateTheme({ density });
  };

  const toggleAnimations = () => {
    updateTheme({ animations: !themeConfig.animations });
  };

  const toggleHighContrast = () => {
    updateTheme({ highContrast: !themeConfig.highContrast });
  };

  return {
    themeConfig,
    currentTheme,
    toggleDarkMode,
    setAutoMode,
    updatePrimaryColor,
    updateSecondaryColor,
    updateBorderRadius,
    updateFontSize,
    updateDensity,
    toggleAnimations,
    toggleHighContrast
  };
};

export default AdvancedThemeProvider; 